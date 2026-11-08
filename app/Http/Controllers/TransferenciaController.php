<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\Transferencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TransferenciaController extends Controller
{
    /**
     * Listado histórico de transferencias bancarias.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $canal = $request->input('canal');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $transferencias = Transferencia::query()
            ->with(['cuentaOrigen.cliente', 'cuentaDestino.cliente', 'usuario'])
            ->when($search, function ($query, $search) {
                $query->where('referencia', 'like', "%{$search}%")
                    ->orWhereHas('cuentaOrigen', fn ($q) => $q->where('numero_cuenta', 'like', "%{$search}%"))
                    ->orWhereHas('cuentaDestino', fn ($q) => $q->where('numero_cuenta', 'like', "%{$search}%"))
                    ->orWhereHas('cuentaOrigen.cliente', function ($q) use ($search) {
                        $q->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('cuentaDestino.cliente', function ($q) use ($search) {
                        $q->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%");
                    });
            })
            ->when($canal, fn ($q, $c) => $q->where('canal', $c))
            ->when($fechaInicio, fn ($q, $fi) => $q->whereDate('created_at', '>=', $fi))
            ->when($fechaFin, fn ($q, $ff) => $q->whereDate('created_at', '<=', $ff))
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('transferencias/Index', [
            'transferencias' => $transferencias,
            'filters' => [
                'search' => $search,
                'canal' => $canal,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Formulario interactivo para procesar transferencias entre cuentas.
     */
    public function create(Request $request): Response
    {
        $cuentaOrigenParam = $request->input('origen');
        $cuentaPrevia = null;

        if ($cuentaOrigenParam) {
            $cuentaPrevia = Cuenta::with('cliente')
                ->where('numero_cuenta', $cuentaOrigenParam)
                ->first();
        }

        return Inertia::render('transferencias/Create', [
            'cuentaPrevia' => $cuentaPrevia,
        ]);
    }

    /**
     * Procesa la transferencia atómica entre dos cuentas bancarias.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cuenta_origen' => ['required', 'string', 'exists:cuentas,numero_cuenta'],
            'cuenta_destino' => ['required', 'string', 'exists:cuentas,numero_cuenta', 'different:cuenta_origen'],
            'monto' => ['required', 'numeric', 'min:1'],
            'canal' => ['required', 'in:ventanilla,banca_web,atm'],
            'metodo_autenticacion' => ['required', 'in:identidad,tarjeta_pin,biometria,2fa'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ], [
            'cuenta_destino.different' => 'La cuenta de destino no puede ser la misma cuenta de origen.',
            'cuenta_origen.exists' => 'La cuenta de origen especificada no existe.',
            'cuenta_destino.exists' => 'La cuenta de destino especificada no existe.',
        ]);

        $monto = floatval($validated['monto']);

        $transferencia = DB::transaction(function () use ($validated, $monto, $request) {
            /** @var Cuenta $origen */
            $origen = Cuenta::where('numero_cuenta', $validated['cuenta_origen'])->lockForUpdate()->firstOrFail();
            /** @var Cuenta $destino */
            $destino = Cuenta::where('numero_cuenta', $validated['cuenta_destino'])->lockForUpdate()->firstOrFail();

            // 1. Validar estados
            if ($origen->estado !== 'activa') {
                abort(422, "La cuenta de origen está {$origen->estado}. No se pueden emitir transferencias.");
            }
            if ($destino->estado !== 'activa') {
                abort(422, "La cuenta de destino está {$destino->estado}. No puede recibir transferencias.");
            }

            // 2. Validar saldo suficiente
            if ($origen->saldo < $monto) {
                abort(422, 'Saldo insuficiente en la cuenta de origen (Disponible: Bs. '.number_format($origen->saldo, 2).').');
            }

            // 3. Aplicar débito en origen y crédito en destino
            $saldoPrevioOrigen = $origen->saldo;
            $origen->saldo -= $monto;
            $origen->saldo_contable -= $monto;
            $origen->save();

            $saldoPrevioDestino = $destino->saldo;
            $destino->saldo += $monto;
            $destino->saldo_contable += $monto;
            $destino->save();

            $referencia = 'TRF-'.strtoupper(Str::random(10));

            $transferencia = Transferencia::create([
                'referencia' => $referencia,
                'cuenta_origen_id' => $origen->id,
                'cuenta_destino_id' => $destino->id,
                'monto' => $monto,
                'canal' => $validated['canal'],
                'metodo_autenticacion' => $validated['metodo_autenticacion'],
                'motivo' => $validated['motivo'] ?? 'Traspaso de fondos',
                'estado' => 'completado',
                'saldo_previo_origen' => $saldoPrevioOrigen,
                'saldo_posterior_origen' => $origen->saldo,
                'saldo_previo_destino' => $saldoPrevioDestino,
                'saldo_posterior_destino' => $destino->saldo,
                'usuario_id' => $request->user()?->id,
            ]);

            // Movimiento 1: Débito en cuenta de origen
            Movimiento::create([
                'referencia' => 'MOV-'.strtoupper(Str::random(10)),
                'tipo' => 'transferencia_debito',
                'naturaleza' => 'debito',
                'monto' => $monto,
                'saldo_previo' => $saldoPrevioOrigen,
                'saldo_posterior' => $origen->saldo,
                'canal' => $validated['canal'],
                'descripcion' => "Transferencia enviada a Cta. {$destino->numero_cuenta} (".($validated['motivo'] ?? 'Traspaso').')',
                'cuenta_id' => $origen->id,
                'usuario_id' => $request->user()?->id,
                'transaccion_type' => Transferencia::class,
                'transaccion_id' => $transferencia->id,
            ]);

            // Movimiento 2: Crédito en cuenta de destino
            Movimiento::create([
                'referencia' => 'MOV-'.strtoupper(Str::random(10)),
                'tipo' => 'transferencia_credito',
                'naturaleza' => 'credito',
                'monto' => $monto,
                'saldo_previo' => $saldoPrevioDestino,
                'saldo_posterior' => $destino->saldo,
                'canal' => $validated['canal'],
                'descripcion' => "Transferencia recibida de Cta. {$origen->numero_cuenta} (".($validated['motivo'] ?? 'Traspaso').')',
                'cuenta_id' => $destino->id,
                'usuario_id' => $request->user()?->id,
                'transaccion_type' => Transferencia::class,
                'transaccion_id' => $transferencia->id,
            ]);

            Bitacora::registrar(
                tipoEvento: 'Transferencia Bancaria',
                descripcion: "Transferencia de Bs. {$monto} de Cta {$origen->numero_cuenta} a Cta {$destino->numero_cuenta}. Ref: {$referencia}",
                userId: $request->user()?->id,
                datosAdicionales: ['referencia' => $referencia, 'transferencia_id' => $transferencia->id]
            );

            return $transferencia;
        });

        return redirect()->route('transferencias.show', $transferencia->id)
            ->with('success', 'Transferencia bancaria procesada exitosamente.');
    }

    /**
     * Muestra el comprobante oficial de la transferencia bancaria.
     */
    public function show(Transferencia $transferencia): Response
    {
        $transferencia->load([
            'cuentaOrigen.cliente',
            'cuentaDestino.cliente',
            'usuario',
        ]);

        return Inertia::render('transferencias/Show', [
            'transferencia' => $transferencia,
        ]);
    }
}
