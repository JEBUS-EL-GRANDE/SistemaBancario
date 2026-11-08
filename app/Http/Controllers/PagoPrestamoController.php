<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\CuotaAmortizacion;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PagoPrestamoController extends Controller
{
    /**
     * Muestra la pantalla para procesar el pago de cuotas de préstamos (RF-025).
     */
    public function create(Request $request): Response
    {
        $codigoPrestamo = $request->input('codigo');
        $prestamo = null;

        if ($codigoPrestamo) {
            $prestamo = Prestamo::with(['cliente', 'cuotas' => function ($q) {
                $q->orderBy('numero_cuota');
            }])
                ->where('codigo', $codigoPrestamo)
                ->first();
        }

        return Inertia::render('pagos-prestamo/Create', [
            'prestamoSeleccionado' => $prestamo,
        ]);
    }

    /**
     * Procesa el cobro de una cuota mensual o pago extraordinario (RF-025).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prestamo_id' => ['required', 'exists:prestamos,id'],
            'tipo' => ['required', 'in:cuota_mensual,anticipado,cancelacion_total'],
            'monto' => ['required', 'numeric', 'min:1'],
            'cuota_id' => ['nullable', 'exists:cuota_amortizacions,id'],
        ]);

        $monto = floatval($validated['monto']);

        $pago = DB::transaction(function () use ($validated, $monto, $request) {
            /** @var Prestamo $prestamo */
            $prestamo = Prestamo::where('id', $validated['prestamo_id'])->lockForUpdate()->firstOrFail();

            if (! in_array($prestamo->estado, ['al_dia', 'moroso', 'desembolsado'])) {
                abort(422, "El préstamo no está activo para recibir pagos (Estado: {$prestamo->estado}).");
            }

            $saldoPrevio = $prestamo->saldo_pendiente;
            $capitalAbonado = 0.00;
            $interesAbonado = 0.00;
            $cuotaId = $validated['cuota_id'] ?? null;

            if ($validated['tipo'] === 'cuota_mensual') {
                /** @var CuotaAmortizacion|null $cuota */
                $cuota = null;
                if ($cuotaId) {
                    $cuota = CuotaAmortizacion::where('id', $cuotaId)->lockForUpdate()->first();
                } else {
                    $cuota = CuotaAmortizacion::where('prestamo_id', $prestamo->id)
                        ->whereIn('estado', ['pendiente', 'vencida', 'pagada_parcial'])
                        ->orderBy('numero_cuota')
                        ->lockForUpdate()
                        ->first();
                }

                if ($cuota) {
                    $cuotaId = $cuota->id;
                    $capitalAbonado = min($monto, $cuota->capital);
                    $interesAbonado = max(0, $monto - $capitalAbonado);

                    $cuota->monto_pagado += $monto;
                    $cuota->fecha_pago = now()->toDateString();
                    if ($cuota->monto_pagado >= $cuota->cuota_total) {
                        $cuota->estado = 'pagada';
                    } else {
                        $cuota->estado = 'pagada_parcial';
                    }
                    $cuota->save();
                } else {
                    $capitalAbonado = $monto;
                }
            } else {
                // Pago anticipado o cancelación total va directo al capital
                $capitalAbonado = $monto;
            }

            $saldoPosterior = max(0, $saldoPrevio - $capitalAbonado);
            $prestamo->saldo_pendiente = $saldoPosterior;

            // Si ya no queda saldo pendiente, el préstamo queda cancelado en su totalidad
            if ($saldoPosterior <= 0) {
                $prestamo->estado = 'cancelado';
                // Marcar cuotas restantes como pagadas
                CuotaAmortizacion::where('prestamo_id', $prestamo->id)
                    ->where('estado', '!=', 'pagada')
                    ->update(['estado' => 'pagada', 'fecha_pago' => now()->toDateString()]);
            }

            $prestamo->save();

            $referencia = 'PAG-'.strtoupper(Str::random(10));

            $pago = PagoPrestamo::create([
                'referencia' => $referencia,
                'monto' => $monto,
                'tipo' => $validated['tipo'],
                'capital_abonado' => $capitalAbonado,
                'interes_abonado' => $interesAbonado,
                'saldo_pendiente_previo' => $saldoPrevio,
                'saldo_pendiente_posterior' => $saldoPosterior,
                'prestamo_id' => $prestamo->id,
                'cuota_id' => $cuotaId,
                'cajero_id' => $request->user()?->id,
            ]);

            Bitacora::registrar(
                accion: 'Cobro de Cuota de Préstamo',
                detalles: "Pago de Bs. {$monto} para el préstamo {$prestamo->codigo}. Ref: {$referencia}",
                tablaAfectada: 'pago_prestamos',
                registroId: $pago->id
            );

            return $pago;
        });

        return redirect()->route('pagos-prestamo.show', $pago->id)
            ->with('success', 'Pago de cuota procesado exitosamente.');
    }

    /**
     * Muestra el comprobante del pago de préstamo.
     */
    public function show(PagoPrestamo $pagoPrestamo): Response
    {
        $pagoPrestamo->load(['prestamo.cliente', 'cuota', 'cajero']);

        return Inertia::render('pagos-prestamo/Show', [
            'pago' => $pagoPrestamo,
        ]);
    }
}
