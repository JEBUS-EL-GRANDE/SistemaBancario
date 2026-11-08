<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Deposito;
use App\Models\Movimiento;
use App\Models\TarjetaDebito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CuentaController extends Controller
{
    /**
     * Muestra el listado de cuentas bancarias con filtros.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $tipo = $request->input('tipo');
        $estado = $request->input('estado');

        $user = $request->user();

        $cuentas = Cuenta::query()
            ->with(['cliente'])
            ->when($user?->isCliente(), function ($query) use ($user) {
                $query->whereHas('cliente', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereRaw('LOWER(email) = ?', [strtolower($user->email)]);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where('numero_cuenta', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('nombres', 'ilike', "%{$search}%")
                            ->orWhere('apellidos', 'ilike', "%{$search}%")
                            ->orWhere('numero_documento', 'like', "%{$search}%");
                    });
            })
            ->when($tipo, fn ($query, $t) => $query->where('tipo', $t))
            ->when($estado, fn ($query, $e) => $query->where('estado', $e))
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('cuentas/Index', [
            'cuentas' => $cuentas,
            'filters' => [
                'search' => $search,
                'tipo' => $tipo,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Muestra el formulario para abrir una nueva cuenta bancaria (RF-006).
     */
    public function create(Request $request): Response
    {
        $clienteId = $request->input('cliente_id');
        $clientes = Cliente::where('estado', 'activo')
            ->select('id', 'nombres', 'apellidos', 'numero_documento', 'email')
            ->orderBy('nombres')
            ->get();

        return Inertia::render('cuentas/Create', [
            'clientes' => $clientes,
            'selectedClienteId' => $clienteId ? (int) $clienteId : null,
        ]);
    }

    /**
     * Procesa la apertura de una nueva cuenta bancaria y opcionalmente tarjeta de débito (RF-006, RF-007).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo' => ['required', 'in:ahorro,corriente'],
            'moneda' => ['required', 'in:BOB'],
            'limite_retiro_diario' => ['required', 'numeric', 'min:100', 'max:50000'],
            'deposito_inicial' => ['nullable', 'numeric', 'min:0'],
            'emitir_tarjeta' => ['nullable', 'boolean'],
            'pin_tarjeta' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]+$/'],
        ]);

        $cuenta = DB::transaction(function () use ($validated, $request) {
            // Generar número de cuenta único bancario de 10 dígitos (prefijo 100 para ahorro, 200 para corriente)
            $prefix = $validated['tipo'] === 'ahorro' ? '100' : '200';
            do {
                $numeroCuenta = $prefix.mt_rand(1000000, 9999999);
            } while (Cuenta::where('numero_cuenta', $numeroCuenta)->exists());

            $montoInicial = floatval($validated['deposito_inicial'] ?? 0);

            $cuenta = Cuenta::create([
                'numero_cuenta' => $numeroCuenta,
                'tipo' => $validated['tipo'],
                'moneda' => $validated['moneda'] ?? 'BOB',
                'saldo' => $montoInicial,
                'saldo_contable' => $montoInicial,
                'limite_retiro_diario' => $validated['limite_retiro_diario'] ?? 5000.00,
                'retiro_acumulado_hoy' => 0.00,
                'fecha_reset_retiro' => now()->toDateString(),
                'estado' => 'activa',
                'cliente_id' => $validated['cliente_id'],
                'aperturada_por' => $request->user()?->id,
                'fecha_apertura' => now(),
            ]);

            // Emisión de tarjeta de débito institucional BIN 453288 (RF-007)
            if (! empty($validated['emitir_tarjeta'])) {
                do {
                    $pan = '453288'.str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
                } while (TarjetaDebito::where('numero_tarjeta', $pan)->exists());

                $pin = $validated['pin_tarjeta'] ?? '1234';
                $cvv = (string) mt_rand(100, 999);

                TarjetaDebito::create([
                    'numero_tarjeta' => $pan,
                    'bin' => '453288',
                    'pin_hash' => Hash::make($pin),
                    'fecha_vencimiento' => now()->addYears(4)->toDateString(),
                    'cvv_hash' => Hash::make($cvv),
                    'estado' => 'activa',
                    'cuenta_id' => $cuenta->id,
                    'emitida_por' => $request->user()?->id,
                ]);
            }

            // Registrar depósito inicial si aplica
            if ($montoInicial > 0) {
                $refDeposito = 'DEP-'.strtoupper(Str::random(8));
                $deposito = Deposito::create([
                    'referencia' => $refDeposito,
                    'tipo' => 'efectivo',
                    'monto' => $montoInicial,
                    'estado' => 'completado',
                    'canal' => 'ventanilla',
                    'observacion' => 'Depósito de apertura de cuenta',
                    'cuenta_id' => $cuenta->id,
                    'cajero_id' => $request->user()?->id,
                ]);

                Movimiento::create([
                    'referencia' => 'MOV-'.strtoupper(Str::random(8)),
                    'tipo' => 'deposito',
                    'naturaleza' => 'credito',
                    'monto' => $montoInicial,
                    'saldo_previo' => 0.00,
                    'saldo_posterior' => $montoInicial,
                    'canal' => 'ventanilla',
                    'descripcion' => 'Depósito inicial de apertura',
                    'cuenta_id' => $cuenta->id,
                    'usuario_id' => $request->user()?->id,
                    'transaccion_type' => Deposito::class,
                    'transaccion_id' => $deposito->id,
                ]);
            }

            Bitacora::registrar(
                accion: 'Apertura de Cuenta',
                detalles: "Apertura de cuenta N° {$cuenta->numero_cuenta} ({$cuenta->tipo}) para el cliente ID {$cuenta->cliente_id}",
                tablaAfectada: 'cuentas',
                registroId: $cuenta->id
            );

            return $cuenta;
        });

        return redirect()->route('cuentas.show', $cuenta->id)
            ->with('success', "Cuenta N° {$cuenta->numero_cuenta} abierta exitosamente.");
    }

    /**
     * Muestra el detalle de la cuenta, titular, tarjeta y movimientos recientes.
     */
    public function show(Cuenta $cuenta): Response
    {
        $cuenta->load([
            'cliente',
            'tarjeta',
            'aperturadaPor',
            'movimientos' => function ($q) {
                $q->latest()->limit(30);
            },
        ]);

        return Inertia::render('cuentas/Show', [
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Control del estado de la cuenta (RF-008: Activa, Bloqueada, Inactiva, Cancelada).
     */
    public function cambiarEstado(Request $request, Cuenta $cuenta): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:activa,bloqueada,inactiva,cancelada'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $anterior = $cuenta->estado;
        $cuenta->estado = $validated['estado'];
        $cuenta->save();

        Bitacora::registrar(
            accion: 'Cambio de Estado de Cuenta',
            detalles: "Cuenta {$cuenta->numero_cuenta} cambió de {$anterior} a {$cuenta->estado}. Motivo: {$request->input('motivo', 'No especificado')}",
            tablaAfectada: 'cuentas',
            registroId: $cuenta->id
        );

        return back()->with('success', "Estado de la cuenta actualizado a '{$cuenta->estado}'.");
    }

    /**
     * Endpoint API para buscar cuenta y verificar titular en vivo (RF-012).
     */
    public function buscarCuenta(Request $request): JsonResponse
    {
        $numero = $request->input('numero_cuenta');
        $cuenta = Cuenta::with('cliente')
            ->where('numero_cuenta', $numero)
            ->first();

        if (! $cuenta) {
            return response()->json(['encontrada' => false, 'mensaje' => 'Cuenta no encontrada'], 404);
        }

        return response()->json([
            'encontrada' => true,
            'id' => $cuenta->id,
            'numero_cuenta' => $cuenta->numero_cuenta,
            'tipo' => $cuenta->tipo,
            'moneda' => $cuenta->moneda,
            'saldo' => $cuenta->saldo,
            'estado' => $cuenta->estado,
            'limite_retiro_diario' => $cuenta->limite_retiro_diario,
            'retiro_acumulado_hoy' => $cuenta->retiro_acumulado_hoy,
            'titular' => $cuenta->cliente->nombreCompleto(),
            'documento_titular' => $cuenta->cliente->numero_documento,
            'cliente_id' => $cuenta->cliente_id,
        ]);
    }
}
