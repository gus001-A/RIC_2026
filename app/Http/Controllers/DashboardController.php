<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Poliza;
use App\Models\Cuenta;
use App\Models\CajaFondo;
use App\Models\Marcador;
use App\Models\MovimientoPoliza;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // ============================================
        // 📊 ESTADÍSTICAS GENERALES
        // ============================================
        $stats = [
            // Usuarios
            'total_usuarios' => Usuario::count(),
            'usuarios_activos' => Usuario::where('activo', true)->count(),
            'usuarios_inactivos' => Usuario::where('activo', false)->count(),
            
            // Empresas
            'total_empresas' => Empresa::count(),
            'empresas_activas' => Empresa::where('activo', true)->count(),
            'empresas_inactivas' => Empresa::where('activo', false)->count(),
            
            // Personas
            'total_personas' => Persona::count(),
            'personas_activas' => Persona::where('activo', true)->count(),
            'personas_fisicas' => Persona::where('tipo_persona', 'FISICA')->count(),
            'personas_morales' => Persona::where('tipo_persona', 'MORAL')->count(),
            
            // Pólizas
            'total_polizas' => Poliza::count(),
            'polizas_pendientes' => Poliza::where('estatus', 'PENDIENTE')->count(),
            'polizas_abonadas' => Poliza::where('estatus', 'ABONADO')->count(),
            'polizas_liquidadas' => Poliza::where('estatus', 'LIQUIDADO')->count(),
            'polizas_ingresos' => Poliza::where('tipo_poliza', 'INGRESO')->count(),
            'polizas_egresos' => Poliza::where('tipo_poliza', 'EGRESO')->count(),
            
            // Cuentas
            'total_cuentas' => Cuenta::count(),
            'cuentas_en_uso' => Cuenta::where('en_uso', true)->count(),
            'cuentas_deudoras' => Cuenta::where('Naturaleza', 'DEUDORA')->count(),
            'cuentas_acreedoras' => Cuenta::where('Naturaleza', 'ACREEDORA')->count(),
            
            // Cajas
            'total_cajas' => CajaFondo::count(),
            'cajas_activas' => CajaFondo::where('activo', true)->count(),
            
            // Marcadores
            'total_marcadores' => Marcador::count(),
            'marcadores_activos' => Marcador::where('activo', true)->count(),
        ];

        // ============================================
        // 📈 MOVIMIENTOS DEL DÍA
        // ============================================
        $stats['polizas_hoy'] = Poliza::whereDate('fecha_poliza', $hoy)->count();
        $stats['ingresos_hoy'] = Poliza::whereDate('fecha_poliza', $hoy)
            ->where('tipo_poliza', 'INGRESO')
            ->count();
        $stats['egresos_hoy'] = Poliza::whereDate('fecha_poliza', $hoy)
            ->where('tipo_poliza', 'EGRESO')
            ->count();

        // ============================================
        // 💰 FINANZAS - CÁLCULO REAL CON MOVIMIENTOS
        // ============================================
        // Total ingresos (movimientos positivos)
        $totalIngresos = MovimientoPoliza::where('monto', '>', 0)->sum('monto');

        // Total egresos (movimientos negativos)
        $totalEgresos = MovimientoPoliza::where('monto', '<', 0)->sum('monto') * -1;

        // Ingresos del mes
        $ingresosMes = MovimientoPoliza::whereBetween('created_at', [$inicioMes, $finMes])
            ->where('monto', '>', 0)
            ->sum('monto');

        // Egresos del mes
        $egresosMes = MovimientoPoliza::whereBetween('created_at', [$inicioMes, $finMes])
            ->where('monto', '<', 0)
            ->sum('monto') * -1;

        $stats['total_ingresos'] = $totalIngresos;
        $stats['total_egresos'] = $totalEgresos;
        $stats['balance'] = $totalIngresos - $totalEgresos;
        $stats['ingresos_mes'] = $ingresosMes;
        $stats['egresos_mes'] = $egresosMes;
        $stats['balance_mes'] = $ingresosMes - $egresosMes;

        // ============================================
        // 🆕 ÚLTIMOS MOVIMIENTOS - CORREGIDO ✅
        // ============================================
        $ultimasPolizas = Poliza::with(['persona', 'usuarioCreador', 'movimientos'])
            ->orderBy('fecha_poliza', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function($poliza) {
                // Calcular el total de la póliza sumando los movimientos
                $total = $poliza->movimientos->sum('monto');
                
                return [
                    'id' => $poliza->id,
                    'folio' => $poliza->folio,
                    'tipo' => $poliza->tipo_poliza,
                    'tipo_texto' => $poliza->tipo_poliza_texto ?? $poliza->tipo_poliza,
                    'estatus' => $poliza->estatus,
                    'estatus_texto' => $poliza->estado_texto ?? $poliza->estatus,
                    // ✅ CORREGIDO: Obtener nombre completo de la persona
                    'persona' => $poliza->persona?->nombre_completo ?? $poliza->persona?->Nombre ?? 'Sin persona',
                    'fecha' => $poliza->fecha_poliza ? $poliza->fecha_poliza->format('d/m/Y H:i') : 'Sin fecha',
                    'total' => number_format($total, 2),
                    'monto' => $total,
                ];
            });

        // ============================================
        // 📊 ESTADÍSTICAS DE PÓLIZAS POR MES
        // ============================================
        $polizasPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $nombreMes = $mes->format('M');
            
            $total = Poliza::whereYear('fecha_poliza', $mes->year)
                ->whereMonth('fecha_poliza', $mes->month)
                ->count();
            
            $ingresos = Poliza::whereYear('fecha_poliza', $mes->year)
                ->whereMonth('fecha_poliza', $mes->month)
                ->where('tipo_poliza', 'INGRESO')
                ->count();
            
            $egresos = Poliza::whereYear('fecha_poliza', $mes->year)
                ->whereMonth('fecha_poliza', $mes->month)
                ->where('tipo_poliza', 'EGRESO')
                ->count();
            
            $polizasPorMes[] = [
                'mes' => $nombreMes,
                'total' => $total,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
            ];
        }

        // ============================================
        // 🔔 ACTIVIDAD RECIENTE
        // ============================================
        $actividadReciente = [];
        
        $polizasRecientes = Poliza::with(['usuarioCreador', 'persona'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($polizasRecientes as $poliza) {
            $actividadReciente[] = [
                'tipo' => 'poliza_creada',
                'titulo' => 'Nueva póliza creada',
                'descripcion' => "Folio: {$poliza->folio} - " . ($poliza->tipo_poliza_texto ?? $poliza->tipo_poliza),
                'persona' => $poliza->persona?->nombre_completo ?? 'Sin persona',
                'usuario' => $poliza->usuarioCreador?->nombre_completo ?? 'Sistema',
                'fecha' => $poliza->created_at->diffForHumans(),
                'fecha_raw' => $poliza->created_at->format('Y-m-d H:i:s'),
                'icono' => $poliza->tipo_poliza === 'INGRESO' ? 'arrow-up' : 'arrow-down',
                'color' => $poliza->tipo_poliza === 'INGRESO' ? 'green' : 'red',
            ];
        }

        // ============================================
        // 🏢 EMPRESAS DEL USUARIO
        // ============================================
        $empresasUsuario = $usuario->empresas()->get()->map(function($empresa) {
            return [
                'id' => $empresa->id,
                'nombre' => $empresa->nombre_empresa,
                'rfc' => $empresa->rfc,
                'activo' => $empresa->activo,
                'total_polizas' => Poliza::where('id_empresa', $empresa->id)->count(),
                'polizas_pendientes' => Poliza::where('id_empresa', $empresa->id)
                    ->where('estatus', 'PENDIENTE')
                    ->count(),
            ];
        });

        return Inertia::render('Dashboard', [
            'usuario' => $usuario,
            'stats' => $stats,
            'ultimasPolizas' => $ultimasPolizas,
            'empresasUsuario' => $empresasUsuario,
            'polizasPorMes' => $polizasPorMes,
            'actividadReciente' => $actividadReciente,
        ]);
    }
}