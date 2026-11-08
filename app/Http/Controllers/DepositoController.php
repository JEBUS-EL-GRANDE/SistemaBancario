<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cuenta;
use App\Models\Deposito;
use App\Models\Movimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DepositoController extends Controller
{
    /**
     * Listado de depósitos registrados con filtros.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $depositos = Deposito::query()
            ->with(['cuenta.cliente', 'cajero'])
            ->when($search, function ($query, $search) {
                $query->where('referencia', 'like', "%{$search}%")
                    ->orWhereHas('cuenta', fn ($q) => $q->where('numero_cuenta', 'like', "%{$search}%"))
                    ->orWhereHas('cuenta.cliente', function ($q) use ($search) {
                        $q->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%");
                    });
            })
            ->when($tipo, fn ($q, $t) => $q->where('tipo', $t))
            ->when($fechaInicio, fn ($q, $fi) => $q->whereDate('created_at', '>=', $fi))
            ->when($fechaFin, fn ($q, $ff) => $q->whereDate('created_at', '<=', $ff))
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('depositos/Index', [
            'depositos' => $depositos,
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Muestra el formulario para registrar un depósito en ventanilla o ATM (RF-009, RF-010, RF-012).
     */
    public function create(Request $request): Response
    {
        $numeroCuenta = $request->input('cuenta');
        $cuentaPrevia = null;

        if ($numeroCuenta) {
            $cuentaPrevia = Cuenta::with('cliente')
                ->where('numero_cuenta', $numeroCuenta)
                ->first();
        }

        return Inertia::render('depositos/Create', [
            'cuentaPrevia' => $cuentaPrevia,
        ]);
    }

    /**
     * Procesa la transacción atómica de depósito (RF-009 al RF-013).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_cuenta' => ['required', 'string', 'exists:cuentas,numero_cuenta'],
            'tipo' => ['required', 'in:efectivo,cheque,transferencia'],
            'monto' => ['required', 'numeric', 'min:1'],
            'canal' => ['required', 'in:ventanilla,atm,sistema'],
            'observacion' => ['nullable', 'string', 'max:255'],
            // Campos obligatorios si el depósito es en cheque (RF-010)
            'banco_emisor' => ['required_if:tipo,cheque', 'nullable', 'string', 'max:100'],
            'numero_cheque' => ['required_if:tipo,cheque', 'nullable', 'string', 'max:50'],
        ]);

        $deposito = DB::transaction(function () use ($validated, $request) {
            /** @var Cuenta $cuenta */
            $cuenta = Cuenta::where('numero_cuenta', $validated['numero_cuenta'])->lockForUpdate()->firstOrFail();

            if ($cuenta->estado !== 'activa') {
                abort(422, "No se puede depositar en una cuenta con estado '{$cuenta->estado}'.");
            }

            $monto = floatval($validated['monto']);
            $esCheque = $validated['tipo'] === 'cheque';
            $estado = $esCheque ? 'en_reserva' : 'completado';

            $saldoAnterior = $cuenta->saldo;

            // Actualización de saldos en tiempo real (RF-011)
            if (! $esCheque) {
                $cuenta->saldo += $monto;
                $cuenta->saldo_contable += $monto;
                $cuenta->save();
            } else {
                // Cheque incrementa saldo contable pero saldo disponible queda retenido (en reserva)
                $cuenta->saldo_contable += $monto;
                $cuenta->save();
            }

            $referencia = 'DEP-'.strtoupper(Str::random(10));

            $deposito = Deposito::create([
                'referencia' => $referencia,
                'tipo' => $validated['tipo'],
                'monto' => $monto,
                'estado' => $estado,
                'banco_emisor' => $validated['banco_emisor'] ?? null,
                'numero_cheque' => $validated['numero_cheque'] ?? null,
                'fecha_liberacion_cheque' => $esCheque ? now()->addDays(2)->toDateString() : null,
                'canal' => $validated['canal'],
                'observacion' => $validated['observacion'] ?? null,
                'cuenta_id' => $cuenta->id,
                'cajero_id' => $request->user()?->id,
            ]);

            // Registrar movimiento en el libro mayor bancario
            Movimiento::create([
                'referencia' => 'MOV-'.strtoupper(Str::random(10)),
                'tipo' => 'deposito',
                'naturaleza' => 'credito',
                'monto' => $monto,
                'saldo_previo' => $saldoAnterior,
                'saldo_posterior' => $cuenta->saldo,
                'canal' => $validated['canal'],
                'descripcion' => "Depósito en {$validated['tipo']} ".($esCheque ? '(Fondos en reserva)' : ''),
                'cuenta_id' => $cuenta->id,
                'usuario_id' => $request->user()?->id,
                'transaccion_type' => Deposito::class,
                'transaccion_id' => $deposito->id,
            ]);

            Bitacora::registrar(
                accion: 'Registro de Depósito',
                detalles: "Depósito de Bs. {$monto} en cuenta {$cuenta->numero_cuenta} ({$validated['tipo']}). Ref: {$referencia}",
                tablaAfectada: 'depositos',
                registroId: $deposito->id
            );

            return $deposito;
        });

        return redirect()->route('depositos.show', $deposito->id)
            ->with('success', 'Depósito procesado exitosamente.');
    }

    /**
     * Muestra el comprobante digital de depósito (RF-013).
     */
    public function show(Deposito $deposito): Response
    {
        $deposito->load(['cuenta.cliente', 'cajero']);

        return Inertia::render('depositos/Show', [
            'deposito' => $deposito,
        ]);
    }
}
