<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\Retiro;
use App\Models\TarjetaDebito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RetiroController extends Controller
{
    /**
     * Listado histórico de retiros.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $canal = $request->input('canal');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $retiros = Retiro::query()
            ->with(['cuenta.cliente', 'cajero'])
            ->when($search, function ($query, $search) {
                $query->where('referencia', 'like', "%{$search}%")
                    ->orWhereHas('cuenta', fn ($q) => $q->where('numero_cuenta', 'like', "%{$search}%"))
                    ->orWhereHas('cuenta.cliente', function ($q) use ($search) {
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

        return Inertia::render('retiros/Index', [
            'retiros' => $retiros,
            'filters' => [
                'search' => $search,
                'canal' => $canal,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Formulario interactivo para procesar retiros en ventanilla o simulador ATM (RF-014, RF-015, RF-016).
     */
    public function create(Request $request): Response
    {
        $numeroCuenta = $request->input('cuenta');
        $cuentaPrevia = null;

        if ($numeroCuenta) {
            $cuentaPrevia = Cuenta::with(['cliente', 'tarjeta'])
                ->where('numero_cuenta', $numeroCuenta)
                ->first();
            if ($cuentaPrevia) {
                $cuentaPrevia->resetearRetiroDiarioSiNecesario();
            }
        }

        return Inertia::render('retiros/Create', [
            'cuentaPrevia' => $cuentaPrevia,
        ]);
    }

    /**
     * Procesa la transacción atómica de retiro con todas las validaciones bancarias (RF-014 al RF-019).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_cuenta' => ['required', 'string', 'exists:cuentas,numero_cuenta'],
            'monto' => ['required', 'numeric', 'min:10'],
            'canal' => ['required', 'in:ventanilla,atm'],
            'metodo_autenticacion' => ['required', 'in:identidad,tarjeta_pin,biometria,2fa'],
            'pin' => ['nullable', 'string', 'size:4'],
            'biometric_token' => ['nullable', 'string'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $monto = floatval($validated['monto']);

        $retiro = DB::transaction(function () use ($validated, $monto, $request) {
            /** @var Cuenta $cuenta */
            $cuenta = Cuenta::with('cliente')->where('numero_cuenta', $validated['numero_cuenta'])->lockForUpdate()->firstOrFail();

            // 1. Verificar estado de la cuenta
            if ($cuenta->estado !== 'activa') {
                abort(422, "La cuenta está {$cuenta->estado}. No se permiten retiros.");
            }

            // 2. Resetear límite diario si corresponde
            $cuenta->resetearRetiroDiarioSiNecesario();

            // 3. Validación de saldo suficiente (RF-017)
            if ($cuenta->saldo < $monto) {
                abort(422, 'Saldo insuficiente. El saldo disponible es de Bs. '.number_format($cuenta->saldo, 2));
            }

            // 4. Validación de límite diario de retiro (RF-018)
            $nuevoAcumulado = $cuenta->retiro_acumulado_hoy + $monto;
            if ($nuevoAcumulado > $cuenta->limite_retiro_diario) {
                $disponibleHoy = $cuenta->limite_retiro_diario - $cuenta->retiro_acumulado_hoy;
                abort(422, 'Supera el límite diario de retiro (Límite: Bs. '.number_format($cuenta->limite_retiro_diario, 2).', disponible hoy: Bs. '.number_format(max(0, $disponibleHoy), 2).').');
            }

            // 5. Validación Biométrica de Huella Dactilar (RF-016)
            if ($validated['metodo_autenticacion'] === 'biometria') {
                $cliente = $cuenta->cliente;
                if (! $cliente || ! $cliente->huellas()->where('estado', 'activo')->exists()) {
                    abort(422, "El titular ({$cliente?->nombreCompleto()}) no cuenta con huellas dactilares enroladas en el sistema biométrico bancario.");
                }
                if (empty($validated['biometric_token'])) {
                    abort(422, 'Se requiere la lectura y verificación biométrica positiva de la huella digital para autorizar este retiro.');
                }
            }

            // 6. Validación de PIN si el canal es ATM o método es tarjeta_pin (RF-015)
            $tarjetaId = null;
            if ($validated['canal'] === 'atm' || $validated['metodo_autenticacion'] === 'tarjeta_pin') {
                $tarjeta = TarjetaDebito::where('cuenta_id', $cuenta->id)->first();
                if (! $tarjeta) {
                    abort(422, 'Esta cuenta no tiene una tarjeta de débito asociada.');
                }
                if ($tarjeta->estado !== 'activa') {
                    abort(422, "La tarjeta de débito asociada está {$tarjeta->estado}.");
                }
                if (empty($validated['pin']) || ! Hash::check($validated['pin'], $tarjeta->pin_hash)) {
                    $tarjeta->increment('intentos_fallidos');
                    if ($tarjeta->intentos_fallidos >= 3) {
                        $tarjeta->estado = 'bloqueada';
                        $tarjeta->save();
                        abort(422, 'PIN incorrecto. La tarjeta ha sido bloqueada por 3 intentos fallidos.');
                    }
                    abort(422, "PIN de tarjeta incorrecto. Intentos fallidos: {$tarjeta->intentos_fallidos}/3.");
                }
                // Si el PIN es correcto, reiniciar intentos fallidos
                $tarjeta->intentos_fallidos = 0;
                $tarjeta->save();
                $tarjetaId = $tarjeta->id;
            }

            // 6. Aplicar débito
            $saldoPrevio = $cuenta->saldo;
            $cuenta->saldo -= $monto;
            $cuenta->saldo_contable -= $monto;
            $cuenta->retiro_acumulado_hoy = $nuevoAcumulado;
            $cuenta->save();

            $referencia = 'RET-'.strtoupper(Str::random(10));
            $notificacionEnviada = $monto >= 2000.00; // Umbral de notificación de seguridad (RF-019)

            $observacionFinal = $validated['observacion'] ?? null;
            if (! empty($validated['biometric_token'])) {
                $observacionFinal = trim(($observacionFinal ? $observacionFinal.' | ' : '')."[Verif. Biométrica: {$validated['biometric_token']}]");
            }

            $retiro = Retiro::create([
                'referencia' => $referencia,
                'monto' => $monto,
                'canal' => $validated['canal'],
                'metodo_autenticacion' => $validated['metodo_autenticacion'],
                'estado' => 'completado',
                'saldo_previo' => $saldoPrevio,
                'saldo_posterior' => $cuenta->saldo,
                'notificacion_enviada' => $notificacionEnviada,
                'observacion' => $observacionFinal,
                'cuenta_id' => $cuenta->id,
                'cajero_id' => $request->user()?->id,
                'tarjeta_id' => $tarjetaId,
            ]);

            // Movimiento bancario
            Movimiento::create([
                'referencia' => 'MOV-'.strtoupper(Str::random(10)),
                'tipo' => 'retiro',
                'naturaleza' => 'debito',
                'monto' => $monto,
                'saldo_previo' => $saldoPrevio,
                'saldo_posterior' => $cuenta->saldo,
                'canal' => $validated['canal'],
                'descripcion' => "Retiro en {$validated['canal']} vía {$validated['metodo_autenticacion']}",
                'cuenta_id' => $cuenta->id,
                'usuario_id' => $request->user()?->id,
                'transaccion_type' => Retiro::class,
                'transaccion_id' => $retiro->id,
            ]);

            Bitacora::registrar(
                accion: 'Registro de Retiro',
                detalles: "Retiro de Bs. {$monto} de la cuenta {$cuenta->numero_cuenta}. Ref: {$referencia}",
                tablaAfectada: 'retiros',
                registroId: $retiro->id
            );

            return $retiro;
        });

        return redirect()->route('retiros.show', $retiro->id)
            ->with('success', 'Retiro completado exitosamente.');
    }

    /**
     * Muestra el comprobante digital de retiro.
     */
    public function show(Retiro $retiro): Response
    {
        $retiro->load(['cuenta.cliente', 'cajero', 'tarjeta']);

        return Inertia::render('retiros/Show', [
            'retiro' => $retiro,
        ]);
    }
}
