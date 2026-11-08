<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Deposito;
use App\Models\Prestamo;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con métricas adaptadas al rol del usuario.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $stats = match ($user->rol) {
            'administrador' => $this->statsAdministrador(),
            'cajero' => $this->statsCajero($user->id),
            'ejecutivo_credito' => $this->statsEjecutivo($user->id),
            'cliente' => $this->statsCliente($user),
            default => [],
        };

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function statsAdministrador(): array
    {
        return [
            'clientes_registrados' => Cliente::count(),
            'cuentas_activas' => Cuenta::where('estado', 'activa')->count(),
            'depositos_hoy' => Deposito::whereDate('created_at', today())->count(),
            'monto_depositos_hoy' => (float) Deposito::whereDate('created_at', today())->sum('monto'),
            'retiros_hoy' => Retiro::whereDate('created_at', today())->where('estado', 'completado')->count(),
            'monto_retiros_hoy' => (float) Retiro::whereDate('created_at', today())->where('estado', 'completado')->sum('monto'),
            'prestamos_activos' => Prestamo::whereIn('estado', ['desembolsado', 'al_dia', 'moroso'])->count(),
            'cartera_total' => (float) Prestamo::whereIn('estado', ['desembolsado', 'al_dia', 'moroso'])->sum('saldo_pendiente'),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function statsCajero(int $cajeroId): array
    {
        return [
            'depositos_hoy' => Deposito::whereDate('created_at', today())->where('cajero_id', $cajeroId)->count(),
            'monto_depositos_hoy' => (float) Deposito::whereDate('created_at', today())->where('cajero_id', $cajeroId)->sum('monto'),
            'retiros_hoy' => Retiro::whereDate('created_at', today())->where('cajero_id', $cajeroId)->where('estado', 'completado')->count(),
            'monto_retiros_hoy' => (float) Retiro::whereDate('created_at', today())->where('cajero_id', $cajeroId)->where('estado', 'completado')->sum('monto'),
            'cuentas_consultadas' => 0,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function statsEjecutivo(int $ejecutivoId): array
    {
        return [
            'solicitudes_pendientes' => Prestamo::where('estado', 'solicitado')->count(),
            'aprobados_hoy' => Prestamo::whereDate('fecha_aprobacion', today())->where('ejecutivo_id', $ejecutivoId)->count(),
            'cartera_activa' => (float) Prestamo::where('ejecutivo_id', $ejecutivoId)->whereIn('estado', ['desembolsado', 'al_dia'])->sum('saldo_pendiente'),
            'en_mora' => Prestamo::where('ejecutivo_id', $ejecutivoId)->where('estado', 'moroso')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function statsCliente(object $user): array
    {
        $cliente = Cliente::where('user_id', $user->id)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->with(['cuentas.tarjeta', 'prestamos'])
            ->first();

        if (! $cliente) {
            return [
                'saldo_total' => 0.00,
                'cuentas_activas' => 0,
                'prestamos_activos' => 0,
                'deuda_total' => 0.00,
                'mis_cuentas' => [],
            ];
        }

        $cuentasActivas = $cliente->cuentas->where('estado', 'activa');
        $prestamosActivos = $cliente->prestamos->whereIn('estado', ['desembolsado', 'al_dia', 'moroso']);

        return [
            'cliente_id' => $cliente->id,
            'saldo_total' => (float) $cuentasActivas->sum('saldo'),
            'cuentas_activas' => $cuentasActivas->count(),
            'prestamos_activos' => $prestamosActivos->count(),
            'deuda_total' => (float) $prestamosActivos->sum('saldo_pendiente'),
            'mis_cuentas' => $cuentasActivas->values()->toArray(),
        ];
    }
}
