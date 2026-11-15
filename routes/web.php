<?php

use App\Http\Controllers\BiometriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\PagoPrestamoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RetiroController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Panel de Control
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Gestión de Clientes (RF-004, RF-005) - Reservado a personal bancario
    Route::middleware('rol:administrador,cajero,ejecutivo_credito')->group(function () {
        Route::post('clientes/{cliente}/acceso-web', [ClienteController::class, 'habilitarAccesoWeb'])->name('clientes.acceso-web');
        Route::resource('clientes', ClienteController::class);

        // Módulo de Biometría y Huellas Dactilares (RF-016)
        Route::get('api/biometria/cliente/{cliente}', [BiometriaController::class, 'clienteHuellas'])->name('biometria.cliente');
        Route::post('api/biometria/enrolar', [BiometriaController::class, 'enrolar'])->name('biometria.enrolar');
        Route::post('api/biometria/verificar', [BiometriaController::class, 'verificar'])->name('biometria.verificar');
        Route::delete('api/biometria/{huella}', [BiometriaController::class, 'destroy'])->name('biometria.destroy');
    });

    // Módulo de Gestión de Cuentas: Búsqueda (validación de destino en transferencias)
    Route::get('api/cuentas/buscar', [CuentaController::class, 'buscarCuenta'])->name('cuentas.buscar');

    // Operaciones exclusivas de Caja y Administración (Apertura de cuentas, depósitos y retiros en ventanilla)
    Route::middleware('rol:administrador,cajero')->group(function () {
        Route::get('cuentas/create', [CuentaController::class, 'create'])->name('cuentas.create');
        Route::post('cuentas', [CuentaController::class, 'store'])->name('cuentas.store');
        Route::post('cuentas/{cuenta}/estado', [CuentaController::class, 'cambiarEstado'])->name('cuentas.estado');

        // Módulo de Depósitos (RF-009 al RF-013)
        Route::resource('depositos', DepositoController::class)->only(['index', 'create', 'store', 'show']);

        // Módulo de Retiros (RF-014 al RF-019)
        Route::resource('retiros', RetiroController::class)->only(['index', 'create', 'store', 'show']);

        // Módulo de Cobro de Préstamos (RF-025)
        Route::resource('pagos-prestamo', PagoPrestamoController::class)->only(['create', 'store', 'show']);
    });

    // Cuentas Bancarias (Listado de sus cuentas para clientes, o todas para personal)
    Route::get('cuentas', [CuentaController::class, 'index'])->name('cuentas.index');
    Route::get('cuentas/{cuenta}', [CuentaController::class, 'show'])->name('cuentas.show');

    // Módulo de Transferencias entre Cuentas (Clientes y Personal)
    Route::resource('transferencias', TransferenciaController::class)->only(['index', 'create', 'store', 'show']);

    // Módulo de Préstamos y Simulador (RF-020 al RF-024)
    Route::get('prestamos/simulador', [PrestamoController::class, 'simulador'])->name('prestamos.simulador');
    Route::post('api/prestamos/simular', [PrestamoController::class, 'calcularSimulacion'])->name('prestamos.calcular');

    // Evaluación y Desembolso de Préstamos - Solo Ejecutivo y Administrador
    Route::middleware('rol:administrador,ejecutivo_credito')->group(function () {
        Route::post('prestamos/{prestamo}/evaluar', [PrestamoController::class, 'evaluar'])->name('prestamos.evaluar');
        Route::post('prestamos/{prestamo}/desembolsar', [PrestamoController::class, 'desembolsar'])->name('prestamos.desembolsar');
        Route::get('prestamos/create', [PrestamoController::class, 'create'])->name('prestamos.create');
        Route::post('prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    });

    Route::get('prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('prestamos/{prestamo}', [PrestamoController::class, 'show'])->name('prestamos.show');

    // Módulo de Reportes y Auditoría (RF-026 al RF-030) - Solo Administrador
    Route::middleware('rol:administrador')->group(function () {
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/exportar', [ReporteController::class, 'exportarCsv'])->name('reportes.exportar');

        // Módulo de Gestión de Usuarios - Solo Administrador (RF-001 al RF-003)
        Route::post('usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
        Route::resource('usuarios', UsuarioController::class)->except(['destroy']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
