<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MotoController;
use App\Http\Controllers\OrdenServicioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\SoloDueno;
use App\Http\Middleware\UsuarioActivo;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', UsuarioActivo::class])->group(function () {

    // ---- Dueño y empleados ----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('productos', ProductoController::class)->only(['index']);
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('clientes', ClienteController::class)->except(['destroy']);
    Route::post('clientes/{cliente}/motos', [MotoController::class, 'store'])->name('clientes.motos.store');
    Route::resource('motos', MotoController::class)->only(['edit', 'update', 'destroy']);

    Route::resource('ordenes', OrdenServicioController::class)
        ->parameters(['ordenes' => 'orden'])
        ->only(['index', 'create', 'store', 'show', 'update']);
    Route::post('ordenes/{orden}/refacciones', [OrdenServicioController::class, 'agregarRefaccion'])->name('ordenes.refacciones.store');
    Route::delete('ordenes/{orden}/refacciones/{detalle}', [OrdenServicioController::class, 'quitarRefaccion'])->name('ordenes.refacciones.destroy');
    Route::patch('ordenes/{orden}/estado', [OrdenServicioController::class, 'cambiarEstado'])->name('ordenes.estado');
    Route::post('ordenes/{orden}/entregar', [OrdenServicioController::class, 'entregar'])->name('ordenes.entregar');
    Route::post('ordenes/{orden}/cancelar', [OrdenServicioController::class, 'cancelar'])->name('ordenes.cancelar');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- Solo el dueño ----
    Route::middleware(SoloDueno::class)->group(function () {
        Route::resource('productos', ProductoController::class)->except(['index', 'show']);
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->except(['show']);
        Route::resource('compras', CompraController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('usuarios', UsuarioController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except(['show', 'destroy']);
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    });
});

require __DIR__.'/auth.php';
