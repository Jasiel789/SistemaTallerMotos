<?php

namespace App\Http\Controllers;

use App\Models\OrdenServicio;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $esDueno = $request->user()->rol === 'dueno';
        $activos = Producto::where('activo', true);

        $abiertas = OrdenServicio::with(['cliente', 'moto'])
            ->whereIn('estado', OrdenServicio::ESTADOS_ABIERTOS);

        return view('dashboard', [
            'esDueno' => $esDueno,
            'totalProductos' => (clone $activos)->count(),
            'stockBajoCount' => (clone $activos)->stockBajo()->count(),
            // Los costos solo se calculan (y se mandan a la vista) para el dueño
            'valorCosto' => $esDueno ? (clone $activos)->sum(DB::raw('stock_actual * precio_compra')) : null,
            'valorVenta' => $esDueno ? (clone $activos)->sum(DB::raw('stock_actual * precio_venta')) : null,
            'ventasHoy' => Venta::where('fecha', '>=', now()->startOfDay())->sum('total'),
            'stockBajo' => (clone $activos)->stockBajo()->orderBy('stock_actual')->limit(8)->get(),
            'ordenesAbiertasCount' => (clone $abiertas)->count(),
            'ordenesAbiertas' => (clone $abiertas)->orderBy('id')->limit(8)->get(),
        ]);
    }
}
