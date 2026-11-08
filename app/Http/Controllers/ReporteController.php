<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Deposito;
use App\Models\Movimiento;
use App\Models\Prestamo;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    /**
     * Muestra la pantalla consolidada de reportes y auditoría bancaria (RF-026 al RF-030).
     */
    public function index(Request $request): Response
    {
        $tipoReporte = $request->input('tipo', 'movimientos');
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        // Resumen general del período
        $totalDepositos = (float) Deposito::whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->where('estado', 'completado')
            ->sum('monto');

        $totalRetiros = (float) Retiro::whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->where('estado', 'completado')
            ->sum('monto');

        $totalCarteraPrestamos = (float) Prestamo::whereIn('estado', ['desembolsado', 'al_dia', 'moroso'])
            ->sum('saldo_pendiente');

        $datos = match ($tipoReporte) {
            'movimientos' => Movimiento::with(['cuenta.cliente', 'usuario'])
                ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),

            'depositos' => Deposito::with(['cuenta.cliente', 'cajero'])
                ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),

            'retiros' => Retiro::with(['cuenta.cliente', 'cajero'])
                ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),

            'prestamos' => Prestamo::with(['cliente', 'ejecutivo'])
                ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),

            'bitacora' => Bitacora::with('usuario')
                ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->latest()
                ->paginate(20)
                ->withQueryString(),

            default => Movimiento::with(['cuenta.cliente', 'usuario'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        };

        return Inertia::render('reportes/Index', [
            'tipoReporte' => $tipoReporte,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'resumen' => [
                'total_depositos' => $totalDepositos,
                'total_retiros' => $totalRetiros,
                'flujo_neto' => $totalDepositos - $totalRetiros,
                'cartera_prestamos' => $totalCarteraPrestamos,
            ],
            'datos' => $datos,
        ]);
    }

    /**
     * Exportación de reportes a archivo CSV estándar (RF-030).
     */
    public function exportarCsv(Request $request): StreamedResponse
    {
        $tipo = $request->input('tipo', 'movimientos');
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $filename = "reporte_{$tipo}_".now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($tipo, $fechaInicio, $fechaFin) {
            $handle = fopen('php://output', 'w');
            // Agregar BOM UTF-8 para visualización correcta en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($tipo === 'movimientos') {
                fputcsv($handle, ['Referencia', 'Fecha', 'Tipo', 'Canal', 'Cuenta', 'Titular', 'Monto (BOB)', 'Saldo Anterior', 'Saldo Nuevo', 'Usuario']);
                Movimiento::with(['cuenta.cliente', 'usuario'])
                    ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                    ->orderBy('id', 'desc')
                    ->chunk(200, function ($movimientos) use ($handle) {
                        foreach ($movimientos as $m) {
                            fputcsv($handle, [
                                $m->referencia,
                                $m->created_at->format('d/m/Y H:i:s'),
                                ucfirst($m->tipo),
                                ucfirst($m->canal),
                                $m->cuenta->numero_cuenta ?? '-',
                                $m->cuenta->cliente?->nombreCompleto() ?? '-',
                                number_format($m->monto, 2, '.', ''),
                                number_format($m->saldo_anterior, 2, '.', ''),
                                number_format($m->saldo_nuevo, 2, '.', ''),
                                $m->usuario->name ?? 'Sistema',
                            ]);
                        }
                    });
            } elseif ($tipo === 'prestamos') {
                fputcsv($handle, ['Código', 'Fecha Solicitud', 'Cliente', 'Documento', 'Tipo', 'Monto Solicitado', 'Monto Aprobado', 'Plazo (Meses)', 'Tasa (%)', 'Cuota Mensual', 'Saldo Pendiente', 'Estado']);
                Prestamo::with('cliente')
                    ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                    ->orderBy('id', 'desc')
                    ->chunk(200, function ($prestamos) use ($handle) {
                        foreach ($prestamos as $p) {
                            fputcsv($handle, [
                                $p->codigo,
                                $p->fecha_solicitud?->format('d/m/Y'),
                                $p->cliente->nombreCompleto(),
                                $p->cliente->numero_documento,
                                ucfirst($p->tipo),
                                number_format($p->monto_solicitado, 2, '.', ''),
                                number_format($p->monto_aprobado ?? 0, 2, '.', ''),
                                $p->plazo_meses,
                                $p->tasa_interes,
                                number_format($p->cuota_mensual ?? 0, 2, '.', ''),
                                number_format($p->saldo_pendiente, 2, '.', ''),
                                ucfirst($p->estado),
                            ]);
                        }
                    });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
