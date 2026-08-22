<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Poliza;
use App\Models\MovimientoPoliza;
use App\Models\AbonoPoliza;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $userId = $user->id_usuario;
        
        $empresaIds = DB::table('empresas_usuarios')
            ->where('id_usuario', $userId)
            ->pluck('id_empresa')
            ->toArray();
        
        if (!empty($empresaIds)) {
            $empresas = \App\Models\Empresa::whereIn('id', $empresaIds)
                ->where('activo', true)
                ->get();
        } else {
            $empresas = collect();
        }
        
        $empresaId = $request->input('empresa_id');
        
        if (!$empresaId) {
            $empresaId = session('empresa_seleccionada');
        }
        
        if ($empresaId) {
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $empresaId)
                ->where('id_usuario', $userId)
                ->exists();
                
            if (!$tieneAcceso) {
                $empresaId = null;
            }
        }
        
        if (!$empresaId && $empresas->count() > 0) {
            $empresaId = $empresas->first()->id;
        }
        
        if ($empresaId) {
            session(['empresa_seleccionada' => $empresaId]);
        }
        
        return Inertia::render('Reportes/Index', [
            'empresas' => $empresas,
            'empresa_seleccionada' => $empresaId,
            'reporte' => [
                'data' => [],
                'fondeadoras' => []
            ],
            'filtros' => $request->only(['fecha_desde', 'fecha_hasta']),
            'vista' => $request->get('vista', 'por_cuenta')
        ]);
    }

    public function getMovimientos(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tipoFiltro = $request->input('tipo_filtro', 'todas');

        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere una empresa'
            ], 422);
        }

        try {
            // 🔥 PRIMERO: OBTENER IDs DE PÓLIZAS QUE CUMPLEN EL FILTRO
            $polizaQuery = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });

            if ($tipoFiltro === 'fiscales') {
                $polizaQuery->where('categoria', 'FISCAL');
            } elseif ($tipoFiltro === 'no_fiscales') {
                $polizaQuery->where('categoria', '!=', 'FISCAL');
            }

            $polizaIds = $polizaQuery->pluck('id')->toArray();

            if (empty($polizaIds)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'fondeadoras' => $this->getCuentasFondeadorasConSaldo($empresaId),
                    'cuentas_resultados' => [],
                    'resultado_utilidad' => 0,
                    'totales_ingresos' => 0,
                    'totales_egresos' => 0,
                    'total_iva' => 0,
                    'total_iva_ingresos' => 0,
                    'total_iva_egresos' => 0,
                    'balance_iva' => 0
                ]);
            }

            // 🔥 OBTENER MOVIMIENTOS SOLO DE PÓLIZAS QUE CUMPLEN EL FILTRO
            $movimientos = MovimientoPoliza::with([
                'poliza.persona',
                'poliza.abonos',
                'cuenta',
                'cuentaFondeadora'
            ])
            ->whereIn('id_poliza', $polizaIds)
            ->get();

            if ($vista === 'por_cuenta') {
                $data = $this->agruparPorCuenta($movimientos);
            } else {
                $data = $this->agruparPorPersona($movimientos);
            }

            // 🔥 CALCULAR TOTALES Y IVA
            $totalIngresos = 0;
            $totalEgresos = 0;
            $totalIva = 0;
            $totalIvaIngresos = 0;
            $totalIvaEgresos = 0;

            foreach ($movimientos as $mov) {
                $monto = (float) $mov->monto;
                
                // 🔥 CALCULAR IVA DEL MOVIMIENTO
                $iva = 0;
                if (isset($mov->monto_iva) && $mov->monto_iva != 0) {
                    $iva = (float) $mov->monto_iva;
                } elseif (isset($mov->iva_dieciseis) && $mov->iva_dieciseis != 0) {
                    $iva = (float) $mov->iva_dieciseis;
                }
                
                if ($iva == 0 && $mov->poliza && $mov->poliza->categoria === 'FISCAL') {
                    $iva = abs($monto) * 0.16;
                }
                
                if ($iva != 0) {
                    if ($monto > 0) {
                        $totalIvaIngresos += abs($iva);
                    } else {
                        $totalIvaEgresos += abs($iva);
                    }
                    $totalIva += abs($iva);
                }
                
                if ($monto > 0) {
                    $totalIngresos += $monto;
                } else {
                    $totalEgresos += abs($monto);
                }
            }

            $cuentasResultadosData = $this->getCuentasResultadosOptimizado(
                $empresaId, 
                $fechaDesde, 
                $fechaHasta, 
                $tipoFiltro,
                $movimientos
            );

            $cuentasResultados = $cuentasResultadosData['cuentas'] ?? [];
            $totalesIvaResultados = $cuentasResultadosData['totales_iva'] ?? 0;

            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId);

            $resultadoUtilidad = $totalIngresos - $totalEgresos;
            $balanceIva = $totalIvaIngresos - $totalIvaEgresos;

            return response()->json([
                'success' => true,
                'data' => $data,
                'fondeadoras' => $fondeadoras,
                'cuentas_resultados' => $cuentasResultados,
                'resultado_utilidad' => $resultadoUtilidad,
                'totales_ingresos' => $totalIngresos,
                'totales_egresos' => $totalEgresos,
                'total_iva' => $totalIva,
                'total_iva_ingresos' => $totalIvaIngresos,
                'total_iva_egresos' => $totalIvaEgresos,
                'balance_iva' => $balanceIva,
                'totales_iva_resultados' => $totalesIvaResultados,
                'tipo_filtro' => $tipoFiltro
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener reporte:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📊 AGRUPAR POR CUENTA
    // ============================================
    private function agruparPorCuenta($movimientos)
    {
        $grupos = [];

        foreach ($movimientos as $mov) {
            $idCuenta = $mov->id_cuenta ?? 'sin_cuenta';
            $nombreCuenta = $mov->cuenta ? $mov->cuenta->nombre_cuenta : 'Sin cuenta';
            $codigoCuenta = $mov->cuenta ? $mov->cuenta->codigo_cuenta : '---';
            $persona = $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona';
            
            $fondeo = 'Sin fondeo';
            if ($mov->cuentaFondeadora) {
                $fondeo = $mov->cuentaFondeadora->nombre_cuenta;
            } else if ($mov->cuenta && $mov->cuenta->fondeo_c == 1) {
                $fondeo = $mov->cuenta->nombre_cuenta;
            }

            $key = $idCuenta . '|' . $persona . '|' . $fondeo;

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'id' => $key,
                    'id_cuenta' => $idCuenta,
                    'nombre' => $nombreCuenta,
                    'codigo' => $codigoCuenta,
                    'persona' => $persona,
                    'fondeo' => $fondeo,
                    'ingreso' => 0,
                    'egreso' => 0
                ];
            }

            if ($mov->monto > 0) {
                $grupos[$key]['ingreso'] += abs($mov->monto);
            } else {
                $grupos[$key]['egreso'] += abs($mov->monto);
            }
        }

        usort($grupos, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });

        return array_values($grupos);
    }

    // ============================================
    // 📊 AGRUPAR POR PERSONA
    // ============================================
    private function agruparPorPersona($movimientos)
    {
        $grupos = [];

        foreach ($movimientos as $mov) {
            $persona = $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona';
            
            $fondeo = 'Sin fondeo';
            if ($mov->cuentaFondeadora) {
                $fondeo = $mov->cuentaFondeadora->nombre_cuenta;
            } else if ($mov->cuenta && $mov->cuenta->fondeo_c == 1) {
                $fondeo = $mov->cuenta->nombre_cuenta;
            }

            $key = $persona . '|' . $fondeo;

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'id' => $key,
                    'nombre' => $persona,
                    'fondeo' => $fondeo,
                    'ingreso' => 0,
                    'egreso' => 0
                ];
            }

            if ($mov->monto > 0) {
                $grupos[$key]['ingreso'] += abs($mov->monto);
            } else {
                $grupos[$key]['egreso'] += abs($mov->monto);
            }
        }

        usort($grupos, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });

        return array_values($grupos);
    }

    private function getCuentasFondeadorasConSaldo($empresaId)
    {
        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'saldo_inicial']);

        if ($cuentas->isEmpty()) {
            return [];
        }

        $resultado = [];
        foreach ($cuentas as $cuenta) {
            $saldo = (float) ($cuenta->saldo_inicial ?? 0);
            
            $resultado[] = [
                'id_cuenta' => $cuenta->id_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'saldo' => $saldo
            ];
        }

        return $resultado;
    }

    // ============================================
    // 📊 GET CUENTAS DE RESULTADOS OPTIMIZADO CON ORDEN INGRESOS/EGRESOS
    // ============================================
    private function getCuentasResultadosOptimizado($empresaId, $fechaDesde = null, $fechaHasta = null, $tipoFiltro = 'todas', $movimientos = null)
    {
        // 🔥 PRIMERO: OBTENER IDs DE PÓLIZAS QUE CUMPLEN EL FILTRO
        $polizaQuery = Poliza::where('id_empresa', $empresaId)
            ->where('es_por_pagar', false)
            ->when($fechaDesde, function($q) use ($fechaDesde) {
                return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function($q) use ($fechaHasta) {
                return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
            });

        if ($tipoFiltro === 'fiscales') {
            $polizaQuery->where('categoria', 'FISCAL');
        } elseif ($tipoFiltro === 'no_fiscales') {
            $polizaQuery->where('categoria', '!=', 'FISCAL');
        }

        $polizaIds = $polizaQuery->pluck('id')->toArray();

        if (empty($polizaIds)) {
            return [
                'cuentas' => [],
                'totales_iva' => 0
            ];
        }

        // 🔥 OBTENER TODAS LAS CUENTAS DE RESULTADOS
        $cuentasResultados = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('es_cuenta_resultados', 1)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'nivel', 'id_cuenta_madre']);

        if ($cuentasResultados->isEmpty()) {
            return [
                'cuentas' => [],
                'totales_iva' => 0
            ];
        }

        // 🔥 OBTENER LAS CUENTAS HIJAS USANDO EL CAMPO cuenta_resultados
        $idsCuentasResultados = $cuentasResultados->pluck('id_cuenta')->toArray();

        $cuentasHijas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->whereIn('cuenta_resultados', $idsCuentasResultados)
            ->whereNotNull('cuenta_resultados')
            ->where('cuenta_resultados', '>', 0)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'nivel', 'id_cuenta_madre', 'cuenta_resultados']);

        // 🔥 SI NO HAY CUENTAS HIJAS, BUSCAR DIRECTAMENTE LAS CUENTAS DE RESULTADOS
        if ($cuentasHijas->isEmpty()) {
            $movimientosQuery = DB::table('movimientos_poliza as mp')
                ->whereIn('mp.id_cuenta', $idsCuentasResultados)
                ->whereIn('mp.id_poliza', $polizaIds)
                ->select(
                    'mp.id_cuenta',
                    DB::raw('SUM(mp.monto) as total'),
                    DB::raw('SUM(mp.monto_iva) as total_iva'),
                    DB::raw('SUM(mp.iva_dieciseis) as total_iva_calculado'),
                    DB::raw('SUM(mp.monto_base) as total_base')
                )
                ->groupBy('mp.id_cuenta');

            $movimientosPorCuenta = $movimientosQuery->get()->keyBy('id_cuenta');
            
            $resultado = [];
            $totalIvaGeneral = 0;

            foreach ($cuentasResultados as $cuenta) {
                $movData = $movimientosPorCuenta[$cuenta->id_cuenta] ?? null;
                $saldo = $movData ? (float) $movData->total : 0;
                
                $iva = 0;
                if ($movData) {
                    $iva = (float) $movData->total_iva;
                    if ($iva == 0) {
                        $iva = (float) $movData->total_iva_calculado;
                    }
                }
                
                if ($iva == 0 && $tipoFiltro === 'fiscales') {
                    $iva = abs($saldo) * 0.16;
                }
                
                if ($saldo != 0 || $iva != 0) {
                    $totalIvaGeneral += abs($iva);
                    $resultado[] = [
                        'id_cuenta' => $cuenta->id_cuenta,
                        'codigo_cuenta' => $cuenta->codigo_cuenta,
                        'nombre_cuenta' => $cuenta->nombre_cuenta,
                        'nivel' => (int) ($cuenta->nivel ?? 2),
                        'id_cuenta_madre' => $cuenta->id_cuenta_madre,
                        'saldo' => $saldo,
                        'iva' => abs($iva),
                        'es_madre' => true,
                        'subtotal' => $saldo,
                        'hijas' => [],
                        'es_fiscal' => ($tipoFiltro === 'fiscales')
                    ];
                }
            }

            // 🔥 ORDENAR: INGRESOS (saldo >= 0) ARRIBA, EGRESOS (saldo < 0) ABAJO
            usort($resultado, function($a, $b) {
                $aEsIngreso = ($a['subtotal'] ?? 0) >= 0;
                $bEsIngreso = ($b['subtotal'] ?? 0) >= 0;
                
                if ($aEsIngreso && !$bEsIngreso) return -1;
                if (!$aEsIngreso && $bEsIngreso) return 1;
                return strcmp($a['codigo_cuenta'] ?? '', $b['codigo_cuenta'] ?? '');
            });

            return [
                'cuentas' => $resultado,
                'totales_iva' => $totalIvaGeneral
            ];
        }

        // 🔥 SI HAY CUENTAS HIJAS, PROCESAR NORMALMENTE
        $idsHijas = $cuentasHijas->pluck('id_cuenta')->toArray();

        $movimientosQuery = DB::table('movimientos_poliza as mp')
            ->whereIn('mp.id_cuenta', $idsHijas)
            ->whereIn('mp.id_poliza', $polizaIds)
            ->select(
                'mp.id_cuenta',
                DB::raw('SUM(mp.monto) as total'),
                DB::raw('SUM(mp.monto_iva) as total_iva'),
                DB::raw('SUM(mp.iva_dieciseis) as total_iva_calculado'),
                DB::raw('SUM(mp.monto_base) as total_base')
            )
            ->groupBy('mp.id_cuenta');

        $movimientosPorCuenta = $movimientosQuery->get()->keyBy('id_cuenta');

        $cuentasConMovimientos = $movimientosPorCuenta->keys()->toArray();

        $hijasConMovimientos = $cuentasHijas->filter(function($hija) use ($cuentasConMovimientos) {
            return in_array($hija->id_cuenta, $cuentasConMovimientos);
        });

        $resultado = [];
        $cuentasHijasPorPadre = $hijasConMovimientos->groupBy('cuenta_resultados');

        $totalIvaGeneral = 0;

        foreach ($cuentasResultados as $padre) {
            $hijas = $cuentasHijasPorPadre->get($padre->id_cuenta, collect());

            if ($hijas->isEmpty()) {
                $movData = $movimientosPorCuenta[$padre->id_cuenta] ?? null;
                if ($movData) {
                    $saldo = (float) $movData->total;
                    $iva = (float) $movData->total_iva;
                    if ($iva == 0) {
                        $iva = (float) $movData->total_iva_calculado;
                    }
                    
                    if ($iva == 0 && $tipoFiltro === 'fiscales') {
                        $iva = abs($saldo) * 0.16;
                    }
                    
                    if ($saldo != 0 || $iva != 0) {
                        $totalIvaGeneral += abs($iva);
                        $resultado[] = [
                            'id_cuenta' => $padre->id_cuenta,
                            'codigo_cuenta' => $padre->codigo_cuenta,
                            'nombre_cuenta' => $padre->nombre_cuenta,
                            'nivel' => (int) ($padre->nivel ?? 2),
                            'id_cuenta_madre' => $padre->id_cuenta_madre,
                            'saldo' => $saldo,
                            'iva' => abs($iva),
                            'es_madre' => true,
                            'subtotal' => $saldo,
                            'hijas' => [],
                            'es_fiscal' => ($tipoFiltro === 'fiscales')
                        ];
                    }
                }
                continue;
            }

            $hijasData = [];
            $subtotal = 0;
            $subtotalIva = 0;

            foreach ($hijas as $hija) {
                $movData = $movimientosPorCuenta[$hija->id_cuenta] ?? null;
                $saldoHija = $movData ? (float) $movData->total : 0;
                
                $ivaHija = 0;
                if ($movData) {
                    $ivaHija = (float) $movData->total_iva;
                    if ($ivaHija == 0) {
                        $ivaHija = (float) $movData->total_iva_calculado;
                    }
                }
                
                if ($ivaHija == 0 && $tipoFiltro === 'fiscales') {
                    $ivaHija = abs($saldoHija) * 0.16;
                }
                
                $subtotal += $saldoHija;
                $subtotalIva += abs($ivaHija);
                
                $hijasData[] = [
                    'id_cuenta' => $hija->id_cuenta,
                    'codigo_cuenta' => $hija->codigo_cuenta,
                    'nombre_cuenta' => $hija->nombre_cuenta,
                    'nivel' => (int) ($hija->nivel ?? 3),
                    'id_cuenta_madre' => $hija->id_cuenta_madre,
                    'saldo' => $saldoHija,
                    'iva' => abs($ivaHija),
                    'es_madre' => false,
                    'subtotal' => $saldoHija,
                    'hijas' => [],
                    'es_fiscal' => ($tipoFiltro === 'fiscales')
                ];
            }

            // 🔥 ORDENAR HIJAS: INGRESOS ARRIBA, EGRESOS ABAJO
            usort($hijasData, function($a, $b) {
                $aEsIngreso = ($a['subtotal'] ?? 0) >= 0;
                $bEsIngreso = ($b['subtotal'] ?? 0) >= 0;
                
                if ($aEsIngreso && !$bEsIngreso) return -1;
                if (!$aEsIngreso && $bEsIngreso) return 1;
                return strcmp($a['codigo_cuenta'] ?? '', $b['codigo_cuenta'] ?? '');
            });

            $totalIvaGeneral += $subtotalIva;

            $resultado[] = [
                'id_cuenta' => $padre->id_cuenta,
                'codigo_cuenta' => $padre->codigo_cuenta,
                'nombre_cuenta' => $padre->nombre_cuenta,
                'nivel' => (int) ($padre->nivel ?? 2),
                'id_cuenta_madre' => $padre->id_cuenta_madre,
                'saldo' => $subtotal,
                'iva' => $subtotalIva,
                'es_madre' => true,
                'subtotal' => $subtotal,
                'hijas' => $hijasData,
                'es_fiscal' => ($tipoFiltro === 'fiscales')
            ];
        }

        // 🔥 ORDENAR CUENTAS PADRE: INGRESOS ARRIBA, EGRESOS ABAJO
        usort($resultado, function($a, $b) {
            $aEsIngreso = ($a['subtotal'] ?? 0) >= 0;
            $bEsIngreso = ($b['subtotal'] ?? 0) >= 0;
            
            if ($aEsIngreso && !$bEsIngreso) return -1;
            if (!$aEsIngreso && $bEsIngreso) return 1;
            return strcmp($a['codigo_cuenta'] ?? '', $b['codigo_cuenta'] ?? '');
        });

        return [
            'cuentas' => $resultado,
            'totales_iva' => $totalIvaGeneral
        ];
    }

    public function getMovimientosCuenta(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $idCuenta = $request->input('id_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tipoFiltro = $request->input('tipo_filtro', 'todas');

        if (!$empresaId || !$idCuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros requeridos'
            ], 422);
        }

        try {
            $cuenta = Cuenta::where('id_empresa', $empresaId)
                ->where('id_cuenta', $idCuenta)
                ->where('en_uso', true)
                ->first();

            if (!$cuenta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta no encontrada'
                ], 404);
            }

            // 🔥 DETECTAR SI ES CUENTA DE RESULTADOS
            $esCuentaResultados = ($cuenta->es_cuenta_resultados == 1);
            $idsCuentas = [];

            if ($esCuentaResultados) {
                $idsHijas = Cuenta::where('id_empresa', $empresaId)
                    ->where('en_uso', true)
                    ->where('cuenta_resultados', $idCuenta)
                    ->whereNotNull('cuenta_resultados')
                    ->where('cuenta_resultados', '>', 0)
                    ->pluck('id_cuenta')
                    ->toArray();
                
                if (empty($idsHijas)) {
                    $idsCuentas = [$idCuenta];
                } else {
                    $idsCuentas = $idsHijas;
                }
            } else {
                $idsCuentas = [$idCuenta];
            }

            // 🔥 PRIMERO: OBTENER IDs DE PÓLIZAS QUE CUMPLEN EL FILTRO
            $polizaQuery = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });

            if ($tipoFiltro === 'fiscales') {
                $polizaQuery->where('categoria', 'FISCAL');
            } elseif ($tipoFiltro === 'no_fiscales') {
                $polizaQuery->where('categoria', '!=', 'FISCAL');
            }

            $polizaIds = $polizaQuery->pluck('id')->toArray();

            if (empty($polizaIds)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total_ingresos' => 0,
                    'total_egresos' => 0,
                    'total_movimientos' => 0,
                    'saldo' => 0,
                    'resultado' => 0,
                    'es_cuenta_resultados' => $esCuentaResultados,
                    'tipo_filtro' => $tipoFiltro
                ]);
            }

            // 🔥 OBTENER MOVIMIENTOS SOLO DE PÓLIZAS QUE CUMPLEN EL FILTRO
            $query = MovimientoPoliza::with([
                'poliza.persona',
                'poliza.abonos',
                'cuenta',
                'cuentaFondeadora'
            ])->whereIn('id_cuenta', $idsCuentas)
            ->whereIn('id_poliza', $polizaIds);

            $movimientos = $query->get();

            if ($movimientos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total_ingresos' => 0,
                    'total_egresos' => 0,
                    'total_movimientos' => 0,
                    'saldo' => 0,
                    'resultado' => 0,
                    'es_cuenta_resultados' => $esCuentaResultados,
                    'tipo_filtro' => $tipoFiltro
                ]);
            }

            // 🔥 AGRUPAR MOVIMIENTOS POR PÓLIZA
            $movimientosAgrupados = [];
            $idsProcesados = [];
            $totalIngresos = 0;
            $totalEgresos = 0;
            $totalIva = 0;

            foreach ($movimientos as $mov) {
                $polizaId = $mov->id_poliza;
                
                if (in_array($polizaId, $idsProcesados)) {
                    continue;
                }
                
                $movimientosPoliza = MovimientoPoliza::where('id_poliza', $polizaId)->get();
                $totalIngresoPoliza = 0;
                $totalEgresoPoliza = 0;
                $montoIvaPoliza = 0;
                $montoBasePoliza = 0;
                
                foreach ($movimientosPoliza as $mp) {
                    if ($mp->monto > 0) {
                        $totalIngresoPoliza += abs($mp->monto);
                    } else {
                        $totalEgresoPoliza += abs($mp->monto);
                    }
                    
                    if (isset($mp->monto_iva) && $mp->monto_iva != 0) {
                        $montoIvaPoliza += abs((float) $mp->monto_iva);
                    } elseif (isset($mp->iva_dieciseis) && $mp->iva_dieciseis != 0) {
                        $montoIvaPoliza += abs((float) $mp->iva_dieciseis);
                    } else {
                        if ($mov->poliza && $mov->poliza->categoria === 'FISCAL') {
                            $montoIvaPoliza += abs($mp->monto) * 0.16;
                        }
                    }
                    $montoBasePoliza += abs((float) ($mp->monto_base ?? 0));
                }
                
                $tipoMovimiento = ($totalIngresoPoliza > $totalEgresoPoliza) ? 'INGRESO' : 'EGRESO';
                $montoTotal = $totalIngresoPoliza - $totalEgresoPoliza;
                $categoria = $mov->poliza->categoria ?? 'NO FISCAL';
                $persona = $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona';
                
                if ($montoTotal != 0) {
                    if ($montoTotal > 0) {
                        $totalIngresos += $montoTotal;
                    } else {
                        $totalEgresos += abs($montoTotal);
                    }
                    $totalIva += $montoIvaPoliza;
                    
                    $movimientosAgrupados[] = [
                        'id_movimiento' => $mov->id,
                        'id_poliza' => $polizaId,
                        'folio' => $mov->poliza->folio ?? 'N/A',
                        'fecha_poliza' => $mov->poliza->fecha_poliza ? $mov->poliza->fecha_poliza->format('Y-m-d') : null,
                        'persona' => $persona,
                        'cuenta' => $mov->cuenta ? $mov->cuenta->nombre_cuenta : 'N/A',
                        'cuenta_fondeadora' => $mov->cuentaFondeadora ? $mov->cuentaFondeadora->nombre_cuenta : 'N/A',
                        'monto' => $montoTotal,
                        'tipo' => $tipoMovimiento,
                        'nota' => $mov->poliza->nota ?? null,
                        'categoria' => $categoria,
                        'monto_iva' => $montoIvaPoliza,
                        'monto_base' => $montoBasePoliza
                    ];
                }
                
                $idsProcesados[] = $polizaId;
            }

            // 🔥 ORDENAR POR FECHA DESCENDENTE
            usort($movimientosAgrupados, function($a, $b) {
                return strcmp($b['fecha_poliza'] ?? '', $a['fecha_poliza'] ?? '');
            });

            $balance = $totalIngresos - $totalEgresos;

            return response()->json([
                'success' => true,
                'data' => $movimientosAgrupados,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'total_movimientos' => count($movimientosAgrupados),
                'saldo' => $balance,
                'resultado' => $balance,
                'total_iva' => $totalIva,
                'es_cuenta_resultados' => $esCuentaResultados,
                'tipo_filtro' => $tipoFiltro
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener movimientos de cuenta:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📊 EXPORTAR EXCEL
    // ============================================
    public function exportExcel(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tipoFiltro = $request->input('tipo_filtro', 'todas');

        if (!$empresaId) {
            return back()->with('error', 'Se requiere una empresa para exportar');
        }

        try {
            $polizaQuery = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });

            if ($tipoFiltro === 'fiscales') {
                $polizaQuery->where('categoria', 'FISCAL');
            } elseif ($tipoFiltro === 'no_fiscales') {
                $polizaQuery->where('categoria', '!=', 'FISCAL');
            }

            $polizaIds = $polizaQuery->pluck('id')->toArray();

            if (empty($polizaIds)) {
                return back()->with('error', 'No hay datos para exportar');
            }

            $movimientos = MovimientoPoliza::with([
                'poliza.persona',
                'poliza.abonos',
                'cuenta',
                'cuentaFondeadora'
            ])
            ->whereIn('id_poliza', $polizaIds)
            ->get();

            if ($vista === 'por_cuenta') {
                $data = $this->agruparPorCuenta($movimientos);
            } else {
                $data = $this->agruparPorPersona($movimientos);
            }

            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId);
            $cuentasResultadosData = $this->getCuentasResultadosOptimizado($empresaId, $fechaDesde, $fechaHasta, $tipoFiltro, $movimientos);
            $cuentasResultados = $cuentasResultadosData['cuentas'] ?? [];
            $ivaTotal = $cuentasResultadosData['totales_iva'] ?? 0;

            $totalIvaIngresos = 0;
            $totalIvaEgresos = 0;
            foreach ($movimientos as $mov) {
                $monto = (float) $mov->monto;
                $iva = 0;
                if (isset($mov->monto_iva) && $mov->monto_iva != 0) {
                    $iva = (float) $mov->monto_iva;
                } elseif (isset($mov->iva_dieciseis) && $mov->iva_dieciseis != 0) {
                    $iva = (float) $mov->iva_dieciseis;
                }
                if ($iva == 0 && $mov->poliza && $mov->poliza->categoria === 'FISCAL') {
                    $iva = abs($monto) * 0.16;
                }
                if ($iva != 0) {
                    if ($monto > 0) {
                        $totalIvaIngresos += abs($iva);
                    } else {
                        $totalIvaEgresos += abs($iva);
                    }
                }
            }
            $balanceIva = $totalIvaIngresos - $totalIvaEgresos;

            $totalIngresos = array_sum(array_column($data, 'ingreso'));
            $totalEgresos = array_sum(array_column($data, 'egreso'));
            $diferencia = $totalIngresos - $totalEgresos;

            $empresa = \App\Models\Empresa::find($empresaId);
            $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Sin empresa';

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Reporte');

            $sheet->setCellValue('A1', 'REPORTE DE MOVIMIENTOS');
            $sheet->setCellValue('A2', 'Empresa: ' . $nombreEmpresa);
            $sheet->setCellValue('A3', 'Fecha: ' . ($fechaDesde ?: 'Inicio') . ' - ' . ($fechaHasta ?: 'Actual'));
            $sheet->setCellValue('A4', 'Vista: ' . ($vista === 'por_cuenta' ? 'Por Cuenta' : 'Por Persona'));
            $sheet->setCellValue('A5', 'Filtro: ' . ucfirst($tipoFiltro));
            
            $sheet->getStyle('A1:A5')->getFont()->setBold(true);
            
            $row = 7;
            if ($vista === 'por_cuenta') {
                $sheet->setCellValue('A' . $row, 'Cuenta');
                $sheet->setCellValue('B' . $row, 'Código');
                $sheet->setCellValue('C' . $row, 'Persona');
                $sheet->setCellValue('D' . $row, 'Fondeo');
                $sheet->setCellValue('E' . $row, 'Ingresos');
                $sheet->setCellValue('F' . $row, 'Egresos');
                $sheet->setCellValue('G' . $row, 'Balance');
            } else {
                $sheet->setCellValue('A' . $row, 'Persona');
                $sheet->setCellValue('B' . $row, 'Fondeo');
                $sheet->setCellValue('C' . $row, 'Ingresos');
                $sheet->setCellValue('D' . $row, 'Egresos');
                $sheet->setCellValue('E' . $row, 'Balance');
            }

            $headerStyle = [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A' . $row . ':' . ($vista === 'por_cuenta' ? 'G' : 'E') . $row)->applyFromArray($headerStyle);
            
            $row++;

            foreach ($data as $item) {
                if ($vista === 'por_cuenta') {
                    $sheet->setCellValue('A' . $row, $item['nombre']);
                    $sheet->setCellValue('B' . $row, $item['codigo'] ?? '---');
                    $sheet->setCellValue('C' . $row, $item['persona']);
                    $sheet->setCellValue('D' . $row, $item['fondeo']);
                    $sheet->setCellValue('E' . $row, $item['ingreso']);
                    $sheet->setCellValue('F' . $row, $item['egreso']);
                    $sheet->setCellValue('G' . $row, $item['ingreso'] - $item['egreso']);
                } else {
                    $sheet->setCellValue('A' . $row, $item['nombre']);
                    $sheet->setCellValue('B' . $row, $item['fondeo']);
                    $sheet->setCellValue('C' . $row, $item['ingreso']);
                    $sheet->setCellValue('D' . $row, $item['egreso']);
                    $sheet->setCellValue('E' . $row, $item['ingreso'] - $item['egreso']);
                }
                $row++;
            }

            $sheet->setCellValue('A' . $row, 'TOTALES');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            if ($vista === 'por_cuenta') {
                $sheet->setCellValue('E' . $row, $totalIngresos);
                $sheet->setCellValue('F' . $row, $totalEgresos);
                $sheet->setCellValue('G' . $row, $diferencia);
            } else {
                $sheet->setCellValue('C' . $row, $totalIngresos);
                $sheet->setCellValue('D' . $row, $totalEgresos);
                $sheet->setCellValue('E' . $row, $diferencia);
            }
            $sheet->getStyle('A' . $row . ':' . ($vista === 'por_cuenta' ? 'G' : 'E') . $row)->getFont()->setBold(true);

            $lastRow = $row;
            for ($i = 7; $i <= $lastRow; $i++) {
                if ($vista === 'por_cuenta') {
                    $sheet->getStyle('E' . $i . ':G' . $i)->getNumberFormat()->setFormatCode('$#,##0.00');
                } else {
                    $sheet->getStyle('C' . $i . ':E' . $i)->getNumberFormat()->setFormatCode('$#,##0.00');
                }
            }

            foreach (range('A', $vista === 'por_cuenta' ? 'G' : 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            if ($tipoFiltro === 'fiscales') {
                $sheetIva = $spreadsheet->createSheet();
                $sheetIva->setTitle('IVA Fiscal');
                $rowIva = 1;
                
                $sheetIva->setCellValue('A' . $rowIva, 'RESUMEN DE IVA');
                $sheetIva->setCellValue('A' . ($rowIva + 1), 'Empresa: ' . $nombreEmpresa);
                $sheetIva->setCellValue('A' . ($rowIva + 2), 'Período: ' . ($fechaDesde ?: 'Inicio') . ' - ' . ($fechaHasta ?: 'Actual'));
                $sheetIva->getStyle('A' . $rowIva . ':A' . ($rowIva + 2))->getFont()->setBold(true);
                
                $rowIva += 4;
                
                $sheetIva->setCellValue('A' . $rowIva, 'Concepto');
                $sheetIva->setCellValue('B' . $rowIva, 'Monto');
                $sheetIva->getStyle('A' . $rowIva . ':B' . $rowIva)->applyFromArray($headerStyle);
                $rowIva++;
                
                $sheetIva->setCellValue('A' . $rowIva, 'IVA Ingresos');
                $sheetIva->setCellValue('B' . $rowIva, $totalIvaIngresos);
                $sheetIva->getStyle('B' . $rowIva)->getNumberFormat()->setFormatCode('$#,##0.00');
                $rowIva++;
                
                $sheetIva->setCellValue('A' . $rowIva, 'IVA Egresos');
                $sheetIva->setCellValue('B' . $rowIva, $totalIvaEgresos);
                $sheetIva->getStyle('B' . $rowIva)->getNumberFormat()->setFormatCode('$#,##0.00');
                $rowIva++;
                
                $sheetIva->setCellValue('A' . $rowIva, 'Balance IVA');
                $sheetIva->setCellValue('B' . $rowIva, $balanceIva);
                $sheetIva->getStyle('A' . $rowIva . ':B' . $rowIva)->getFont()->setBold(true);
                $sheetIva->getStyle('B' . $rowIva)->getNumberFormat()->setFormatCode('$#,##0.00');
                
                foreach (range('A', 'B') as $col) {
                    $sheetIva->getColumnDimension($col)->setAutoSize(true);
                }
            }

            if (!empty($cuentasResultados)) {
                $sheetResultados = $spreadsheet->createSheet();
                $sheetResultados->setTitle('Cuentas de Resultados');
                $rowResultados = 1;
                
                $sheetResultados->setCellValue('A' . $rowResultados, 'CUENTAS DE RESULTADOS');
                $sheetResultados->setCellValue('A' . ($rowResultados + 1), 'Empresa: ' . $nombreEmpresa);
                $sheetResultados->setCellValue('A' . ($rowResultados + 2), 'Filtro: ' . ucfirst($tipoFiltro));
                if ($tipoFiltro === 'fiscales') {
                    $sheetResultados->setCellValue('A' . ($rowResultados + 3), 'Total IVA: $' . number_format($ivaTotal, 2));
                }
                $sheetResultados->getStyle('A' . $rowResultados . ':A' . ($rowResultados + 3))->getFont()->setBold(true);
                
                $rowResultados += 5;
                
                $sheetResultados->setCellValue('A' . $rowResultados, 'Nombre de la Cuenta');
                $sheetResultados->setCellValue('B' . $rowResultados, 'Saldo');
                if ($tipoFiltro === 'fiscales') {
                    $sheetResultados->setCellValue('C' . $rowResultados, 'IVA');
                }
                $sheetResultados->setCellValue(($tipoFiltro === 'fiscales' ? 'D' : 'C') . $rowResultados, 'Nivel');
                
                $headerStyleResultados = [
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ];
                $lastColumn = $tipoFiltro === 'fiscales' ? 'D' : 'C';
                $sheetResultados->getStyle('A' . $rowResultados . ':' . $lastColumn . $rowResultados)->applyFromArray($headerStyleResultados);
                
                $rowResultados++;
                
                // 🔥 PASAR LAS CUENTAS YA ORDENADAS
                $this->renderCuentasJerarquicasExcel($sheetResultados, $cuentasResultados, $rowResultados, 0, $tipoFiltro);
                
                foreach (range('A', $lastColumn) as $col) {
                    $sheetResultados->getColumnDimension($col)->setAutoSize(true);
                }
            }

            if (!empty($fondeadoras)) {
                $sheetFondeadoras = $spreadsheet->createSheet();
                $sheetFondeadoras->setTitle('Fondeadoras');
                $rowFon = 1;
                
                $sheetFondeadoras->setCellValue('A' . $rowFon, 'CUENTAS FONDEADORAS');
                $sheetFondeadoras->setCellValue('A' . ($rowFon + 1), 'Empresa: ' . $nombreEmpresa);
                $sheetFondeadoras->getStyle('A' . $rowFon . ':A' . ($rowFon + 1))->getFont()->setBold(true);
                
                $rowFon += 3;
                
                $sheetFondeadoras->setCellValue('A' . $rowFon, 'Código');
                $sheetFondeadoras->setCellValue('B' . $rowFon, 'Nombre');
                $sheetFondeadoras->setCellValue('C' . $rowFon, 'Saldo');
                
                $headerStyleFon = [
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ];
                $sheetFondeadoras->getStyle('A' . $rowFon . ':C' . $rowFon)->applyFromArray($headerStyleFon);
                
                $rowFon++;
                
                foreach ($fondeadoras as $fon) {
                    $sheetFondeadoras->setCellValue('A' . $rowFon, $fon['codigo_cuenta']);
                    $sheetFondeadoras->setCellValue('B' . $rowFon, $fon['nombre_cuenta']);
                    $sheetFondeadoras->setCellValue('C' . $rowFon, $fon['saldo']);
                    $sheetFondeadoras->getStyle('C' . $rowFon)->getNumberFormat()->setFormatCode('$#,##0.00');
                    $rowFon++;
                }
                
                foreach (range('A', 'C') as $col) {
                    $sheetFondeadoras->getColumnDimension($col)->setAutoSize(true);
                }
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'reporte_completo_' . date('Y-m-d_H-i-s') . '.xlsx';

            $tempPath = storage_path('app/temp/' . $filename);
            if (!is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }
            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error al exportar Excel:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza cuentas jerárquicas en Excel con orden INGRESOS/EGRESOS
     */
    private function renderCuentasJerarquicasExcel($sheet, $cuentas, &$row, $nivel, $tipoFiltro = 'todas')
    {
        // 🔥 ORDENAR: INGRESOS (saldo >= 0) ARRIBA, EGRESOS (saldo < 0) ABAJO
        usort($cuentas, function($a, $b) {
            $aEsIngreso = ($a['subtotal'] ?? 0) >= 0;
            $bEsIngreso = ($b['subtotal'] ?? 0) >= 0;
            
            if ($aEsIngreso && !$bEsIngreso) return -1;
            if (!$aEsIngreso && $bEsIngreso) return 1;
            return strcmp($a['codigo_cuenta'] ?? '', $b['codigo_cuenta'] ?? '');
        });

        foreach ($cuentas as $cuenta) {
            $indent = str_repeat('  ', $nivel);
            
            $sheet->setCellValue('A' . $row, $indent . $cuenta['nombre_cuenta']);
            $sheet->setCellValue('B' . $row, $cuenta['subtotal']);
            if ($tipoFiltro === 'fiscales') {
                $sheet->setCellValue('C' . $row, $cuenta['iva'] ?? 0);
                $sheet->setCellValue('D' . $row, $cuenta['nivel']);
            } else {
                $sheet->setCellValue('C' . $row, $cuenta['nivel']);
            }
            
            if ($cuenta['es_madre']) {
                $lastColumn = $tipoFiltro === 'fiscales' ? 'D' : 'C';
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FF']],
                ]);
            }
            
            if ($tipoFiltro === 'fiscales') {
                $sheet->getStyle('B' . $row . ':C' . $row)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            } else {
                $sheet->getStyle('B' . $row)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            }
            
            $row++;
            
            if (!empty($cuenta['hijas'])) {
                // 🔥 ORDENAR HIJAS TAMBIÉN
                $hijasOrdenadas = $cuenta['hijas'];
                usort($hijasOrdenadas, function($a, $b) {
                    $aEsIngreso = ($a['subtotal'] ?? 0) >= 0;
                    $bEsIngreso = ($b['subtotal'] ?? 0) >= 0;
                    
                    if ($aEsIngreso && !$bEsIngreso) return -1;
                    if (!$aEsIngreso && $bEsIngreso) return 1;
                    return strcmp($a['codigo_cuenta'] ?? '', $b['codigo_cuenta'] ?? '');
                });
                
                $this->renderCuentasJerarquicasExcel($sheet, $hijasOrdenadas, $row, $nivel + 1, $tipoFiltro);
            }
        }
    }

    // ============================================
    // 🔥 EXPORTAR PDF DE RESULTADOS
    // ============================================
    public function exportPdfResultados(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tipoFiltro = $request->input('tipo_filtro', 'todas');

        if (!$empresaId) {
            return back()->with('error', 'Se requiere una empresa para exportar');
        }

        try {
            $movimientos = null;
            $cuentasResultadosData = $this->getCuentasResultadosOptimizado(
                $empresaId, 
                $fechaDesde, 
                $fechaHasta, 
                $tipoFiltro,
                $movimientos
            );
            $cuentasResultados = $cuentasResultadosData['cuentas'] ?? [];
            $ivaTotal = $cuentasResultadosData['totales_iva'] ?? 0;

            if ($tipoFiltro === 'fiscales' && $ivaTotal == 0) {
                $ivaTotal = $this->calcularIvaDirecto($empresaId, $fechaDesde, $fechaHasta);
            }

            if (empty($cuentasResultados)) {
                return back()->with('error', 'No hay cuentas de resultados para exportar');
            }

            $empresa = \App\Models\Empresa::find($empresaId);
            $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Sin empresa';

            $totalCuentas = 0;
            $saldoTotal = 0;
            $totalIngresos = 0;
            $totalEgresos = 0;
            
            foreach ($cuentasResultados as $padre) {
                $totalCuentas += count($padre['hijas']);
                $saldo = (float) ($padre['subtotal'] ?? 0);
                $saldoTotal += $saldo;
                
                if ($saldo > 0) {
                    $totalIngresos += $saldo;
                } else {
                    $totalEgresos += abs($saldo);
                }
            }

            $data = [
                'empresa' => $nombreEmpresa,
                'cuentas' => $cuentasResultados, // YA VIENEN ORDENADAS DEL MÉTODO
                'fecha_desde' => $fechaDesde ?: 'Inicio',
                'fecha_hasta' => $fechaHasta ?: 'Actual',
                'tipo_filtro' => $tipoFiltro,
                'total_cuentas' => $totalCuentas,
                'saldo_total' => $saldoTotal,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'iva_total' => $ivaTotal,
                'fecha_exportacion' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.resultados-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $filename = 'cuentas_resultados_' . date('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error al exportar PDF de resultados:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    // ============================================
    // 🔥 MÉTODO PARA CALCULAR IVA DIRECTAMENTE
    // ============================================
    private function calcularIvaDirecto($empresaId, $fechaDesde = null, $fechaHasta = null)
    {
        $query = MovimientoPoliza::whereHas('poliza', function($q) use ($empresaId, $fechaDesde, $fechaHasta) {
            $q->where('id_empresa', $empresaId)
              ->where('es_por_pagar', false)
              ->where('categoria', 'FISCAL');
            
            if ($fechaDesde) {
                $q->whereDate('fecha_poliza', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $q->whereDate('fecha_poliza', '<=', $fechaHasta);
            }
        });

        $result = $query->select(
            DB::raw('SUM(ABS(monto_iva)) as total_monto_iva'),
            DB::raw('SUM(ABS(iva_dieciseis)) as total_iva_dieciseis')
        )->first();

        $ivaTotal = 0;
        
        if ($result) {
            $ivaTotal = (float) ($result->total_monto_iva ?? 0);
            if ($ivaTotal == 0) {
                $ivaTotal = (float) ($result->total_iva_dieciseis ?? 0);
            }
        }

        return $ivaTotal;
    }
}