<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\CuotaAmortizacion;
use App\Models\Movimiento;
use App\Models\Prestamo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PrestamoController extends Controller
{
    /**
     * Muestra la lista de préstamos y solicitudes con filtros.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');

        $prestamos = Prestamo::query()
            ->with(['cliente', 'ejecutivo', 'cuentaDesembolso'])
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%")
                            ->orWhere('numero_documento', 'like', "%{$search}%");
                    });
            })
            ->when($estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($tipo, fn ($q, $t) => $q->where('tipo', $t))
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('prestamos/Index', [
            'prestamos' => $prestamos,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Muestra el formulario para una nueva solicitud de préstamo (RF-020, RF-021).
     */
    public function create(Request $request): Response
    {
        $clienteId = $request->input('cliente_id');
        $clientes = Cliente::with('cuentas')
            ->where('estado', 'activo')
            ->orderBy('nombres')
            ->get();

        return Inertia::render('prestamos/Create', [
            'clientes' => $clientes,
            'selectedClienteId' => $clienteId ? (int) $clienteId : null,
        ]);
    }

    /**
     * Registra una nueva solicitud y realiza la evaluación crediticia automática (RF-020, RF-021).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'cuenta_desembolso_id' => ['required', 'exists:cuentas,id'],
            'tipo' => ['required', 'in:personal,hipotecario,comercial'],
            'monto_solicitado' => ['required', 'numeric', 'min:500', 'max:1000000'],
            'tasa_interes' => ['required', 'numeric', 'min:1', 'max:50'], // Tasa anual en porcentaje
            'plazo_meses' => ['required', 'integer', 'min:3', 'max:360'],
            'destino_credito' => ['nullable', 'string', 'max:255'],
            'ingreso_mensual' => ['required', 'numeric', 'min:100'],
            'egreso_mensual' => ['required', 'numeric', 'min:0'],
        ]);

        $monto = floatval($validated['monto_solicitado']);
        $tasaAnual = floatval($validated['tasa_interes']);
        $tasaMensual = ($tasaAnual / 100) / 12;
        $plazo = intval($validated['plazo_meses']);

        // Cálculo de cuota fija bajo amortización francesa (RF-023)
        $cuota = $this->calcularCuotaFrancesa($monto, $tasaMensual, $plazo);

        // Capacidad de endeudamiento máxima sugerida: 40% del ingreso neto (RF-021)
        $ingresoNeto = floatval($validated['ingreso_mensual']) - floatval($validated['egreso_mensual']);
        $capacidadPago = max(0, $ingresoNeto * 0.40);

        $codigo = 'PRE-'.strtoupper(Str::random(8));

        $prestamo = Prestamo::create([
            'codigo' => $codigo,
            'tipo' => $validated['tipo'],
            'monto_solicitado' => $monto,
            'monto_aprobado' => null,
            'tasa_interes' => $tasaAnual,
            'plazo_meses' => $plazo,
            'destino_credito' => $validated['destino_credito'] ?? 'Libre disponibilidad',
            'cuota_mensual' => $cuota,
            'saldo_pendiente' => 0.00,
            'estado' => 'solicitado',
            'ingreso_mensual' => $validated['ingreso_mensual'],
            'egreso_mensual' => $validated['egreso_mensual'],
            'capacidad_pago' => $capacidadPago,
            'cliente_id' => $validated['cliente_id'],
            'cuenta_desembolso_id' => $validated['cuenta_desembolso_id'],
            'ejecutivo_id' => $request->user()?->id,
            'fecha_solicitud' => now(),
        ]);

        Bitacora::registrar(
            accion: 'Solicitud de Préstamo',
            detalles: "Nueva solicitud de crédito {$codigo} por Bs. {$monto} para el cliente ID {$validated['cliente_id']}",
            tablaAfectada: 'prestamos',
            registroId: $prestamo->id
        );

        return redirect()->route('prestamos.show', $prestamo->id)
            ->with('success', "Solicitud de préstamo {$codigo} registrada exitosamente.");
    }

    /**
     * Muestra el detalle del préstamo, evaluación, cuotas y pagos.
     */
    public function show(Prestamo $prestamo): Response
    {
        $prestamo->load([
            'cliente',
            'cuentaDesembolso',
            'ejecutivo',
            'cuotas' => function ($q) {
                $q->orderBy('numero_cuota');
            },
            'pagos' => function ($q) {
                $q->latest();
            },
        ]);

        return Inertia::render('prestamos/Show', [
            'prestamo' => $prestamo,
        ]);
    }

    /**
     * Aprobación o Rechazo del préstamo (RF-022). Genera la tabla de amortización si se aprueba.
     */
    public function evaluar(Request $request, Prestamo $prestamo): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:aprobar,rechazar'],
            'monto_aprobado' => ['required_if:decision,aprobar', 'nullable', 'numeric', 'min:100'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['decision'] === 'rechazar') {
            $prestamo->estado = 'rechazado';
            $prestamo->motivo_rechazo = $validated['motivo'] ?? 'No cumple con las políticas de riesgo';
            $prestamo->save();

            Bitacora::registrar(
                accion: 'Rechazo de Préstamo',
                detalles: "Préstamo {$prestamo->codigo} rechazado. Motivo: {$prestamo->motivo_rechazo}",
                tablaAfectada: 'prestamos',
                registroId: $prestamo->id
            );

            return back()->with('success', 'Préstamo rechazado.');
        }

        // Aprobación y generación del plan de amortización francesa (RF-022, RF-023)
        DB::transaction(function () use ($prestamo, $validated, $request) {
            $montoAprobado = floatval($validated['monto_aprobado']);
            $tasaMensual = ($prestamo->tasa_interes / 100) / 12;
            $plazo = $prestamo->plazo_meses;
            $cuotaMensual = $this->calcularCuotaFrancesa($montoAprobado, $tasaMensual, $plazo);

            $prestamo->monto_aprobado = $montoAprobado;
            $prestamo->cuota_mensual = $cuotaMensual;
            $prestamo->saldo_pendiente = $montoAprobado;
            $prestamo->estado = 'aprobado';
            $prestamo->fecha_aprobacion = now();
            $prestamo->ejecutivo_id = $request->user()?->id;
            $prestamo->save();

            // Generar cuotas amortización francesa
            $saldoRestante = $montoAprobado;
            $prestamo->cuotas()->delete();

            for ($i = 1; $i <= $plazo; $i++) {
                $interesCuota = round($saldoRestante * $tasaMensual, 2);
                $capitalCuota = round($cuotaMensual - $interesCuota, 2);

                // Ajuste en la última cuota para evitar desfase de centavos
                if ($i === $plazo || $capitalCuota > $saldoRestante) {
                    $capitalCuota = $saldoRestante;
                    $cuotaTotalAjustada = $capitalCuota + $interesCuota;
                    $saldoRestante = 0.00;
                } else {
                    $saldoRestante = round($saldoRestante - $capitalCuota, 2);
                    $cuotaTotalAjustada = $cuotaMensual;
                }

                CuotaAmortizacion::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'fecha_vencimiento' => now()->addMonths($i)->toDateString(),
                    'cuota_total' => $cuotaTotalAjustada,
                    'capital' => $capitalCuota,
                    'interes' => $interesCuota,
                    'saldo_restante' => max(0, $saldoRestante),
                    'estado' => 'pendiente',
                    'monto_pagado' => 0.00,
                ]);
            }

            Bitacora::registrar(
                accion: 'Aprobación de Préstamo',
                detalles: "Préstamo {$prestamo->codigo} aprobado por Bs. {$montoAprobado} en {$plazo} cuotas.",
                tablaAfectada: 'prestamos',
                registroId: $prestamo->id
            );
        });

        return back()->with('success', 'Préstamo aprobado y tabla de amortización generada.');
    }

    /**
     * Desembolso del préstamo directo a la cuenta del cliente (RF-024).
     */
    public function desembolsar(Request $request, Prestamo $prestamo): RedirectResponse
    {
        if ($prestamo->estado !== 'aprobado') {
            abort(422, 'Solo se pueden desembolsar préstamos con estado aprobado.');
        }

        DB::transaction(function () use ($prestamo, $request) {
            /** @var Cuenta $cuenta */
            $cuenta = Cuenta::where('id', $prestamo->cuenta_desembolso_id)->lockForUpdate()->firstOrFail();

            $monto = $prestamo->monto_aprobado;
            $saldoPrevio = $cuenta->saldo;

            // Abonar a la cuenta del cliente
            $cuenta->saldo += $monto;
            $cuenta->saldo_contable += $monto;
            $cuenta->save();

            $prestamo->estado = 'al_dia';
            $prestamo->fecha_desembolso = now();
            $prestamo->save();

            // Movimiento bancario
            Movimiento::create([
                'referencia' => 'MOV-'.strtoupper(Str::random(10)),
                'tipo' => 'desembolso_prestamo',
                'naturaleza' => 'credito',
                'monto' => $monto,
                'saldo_previo' => $saldoPrevio,
                'saldo_posterior' => $cuenta->saldo,
                'canal' => 'sistema',
                'descripcion' => "Desembolso de Préstamo {$prestamo->codigo}",
                'cuenta_id' => $cuenta->id,
                'usuario_id' => $request->user()?->id,
                'transaccion_type' => Prestamo::class,
                'transaccion_id' => $prestamo->id,
            ]);

            Bitacora::registrar(
                accion: 'Desembolso de Préstamo',
                detalles: "Desembolso de Bs. {$monto} abonado a la cuenta {$cuenta->numero_cuenta} por crédito {$prestamo->codigo}",
                tablaAfectada: 'prestamos',
                registroId: $prestamo->id
            );
        });

        return back()->with('success', 'Préstamo desembolsado exitosamente a la cuenta del cliente.');
    }

    /**
     * Simulador público/interno interactivo de amortización francesa (RF-023).
     */
    public function simulador(Request $request): Response
    {
        return Inertia::render('prestamos/Simulador');
    }

    /**
     * Endpoint API para calcular cuotas y tabla en tiempo real.
     */
    public function calcularSimulacion(Request $request): JsonResponse
    {
        $monto = floatval($request->input('monto', 10000));
        $tasaAnual = floatval($request->input('tasa', 12));
        $plazo = intval($request->input('plazo', 12));

        $tasaMensual = ($tasaAnual / 100) / 12;
        $cuota = $this->calcularCuotaFrancesa($monto, $tasaMensual, $plazo);

        $tabla = [];
        $saldo = $monto;

        for ($i = 1; $i <= $plazo; $i++) {
            $interes = round($saldo * $tasaMensual, 2);
            $capital = round($cuota - $interes, 2);

            if ($i === $plazo || $capital > $saldo) {
                $capital = $saldo;
                $cuotaFinal = $capital + $interes;
                $saldo = 0.00;
            } else {
                $saldo = round($saldo - $capital, 2);
                $cuotaFinal = $cuota;
            }

            $tabla[] = [
                'cuota_nro' => $i,
                'cuota_total' => $cuotaFinal,
                'capital' => $capital,
                'interes' => $interes,
                'saldo_restante' => max(0, $saldo),
            ];
        }

        return response()->json([
            'cuota_mensual' => $cuota,
            'monto' => $monto,
            'tasa_anual' => $tasaAnual,
            'plazo_meses' => $plazo,
            'total_a_pagar' => array_sum(array_column($tabla, 'cuota_total')),
            'total_intereses' => array_sum(array_column($tabla, 'interes')),
            'cronograma' => $tabla,
        ]);
    }

    /**
     * Fórmula matemática del método francés de amortización.
     */
    private function calcularCuotaFrancesa(float $monto, float $tasaMensual, int $plazo): float
    {
        if ($tasaMensual <= 0) {
            return round($monto / $plazo, 2);
        }

        $factor = pow(1 + $tasaMensual, $plazo);
        $cuota = $monto * (($tasaMensual * $factor) / ($factor - 1));

        return round($cuota, 2);
    }
}
