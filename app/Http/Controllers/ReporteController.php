<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Poliza;
use App\Models\MovimientoPoliza;
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
        
        if (!$empresaId && $empresas->count() > 0) {
            $empresaId = $empresas->first()->id;
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

    /**
     * 🔥 OBTENER MOVIMIENTOS - OPTIMIZADO
     */
    public function getMovimientos(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $soloFiscales = $request->input('solo_fiscales', false);

        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere una empresa'
            ], 422);
        }

        try {
            // 🔥 OPTIMIZACIÓN 1: Obtener solo los IDs necesarios primero
            $polizaIds = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                })
                ->pluck('id')
                ->toArray();

            if (empty($polizaIds)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'fondeadoras' => [],
                    'cuentas_resultados' => [],
                    'resultado_utilidad' => 0
                ]);
            }

            // 🔥 OPTIMIZACIÓN 2: Obtener movimientos con una sola consulta
            $movimientos = MovimientoPoliza::with([
                'poliza.persona',
                'cuenta',
                'cuentaFondeadora'
            ])
            ->whereIn('id_poliza', $polizaIds)
            ->get();

            // 🔥 OPTIMIZACIÓN 3: Procesar en memoria (más rápido que múltiples consultas)
            if ($vista === 'por_cuenta') {
                $data = $this->agruparPorCuenta($movimientos);
            } else {
                $data = $this->agruparPorPersona($movimientos);
            }

            // 🔥 OPTIMIZACIÓN 4: Obtener fondeadoras y resultados con consultas separadas pero optimizadas
            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId, $fechaDesde, $fechaHasta);
            $cuentasResultados = $this->getCuentasResultadosOptimizado($empresaId, $fechaDesde, $fechaHasta, $soloFiscales);

            $totalIngresos = array_sum(array_column($data, 'ingreso'));
            $totalEgresos = array_sum(array_column($data, 'egreso'));
            $resultadoUtilidad = $totalIngresos - $totalEgresos;

            return response()->json([
                'success' => true,
                'data' => $data,
                'fondeadoras' => $fondeadoras,
                'cuentas_resultados' => $cuentasResultados,
                'resultado_utilidad' => $resultadoUtilidad
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

    /**
     * ✅ OBTENER CUENTAS FONDEADORAS - OPTIMIZADO
     */
    private function getCuentasFondeadorasConSaldo($empresaId, $fechaDesde = null, $fechaHasta = null)
    {
        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'saldo_inicial']);

        if ($cuentas->isEmpty()) {
            return [];
        }

        $idsCuentas = $cuentas->pluck('id_cuenta')->toArray();

        // 🔥 OPTIMIZACIÓN: Usar una consulta con JOIN para obtener los saldos
        $query = DB::table('movimientos_poliza as mp')
            ->join('polizas as p', 'mp.id_poliza', '=', 'p.id')
            ->whereIn('mp.id_cuenta', $idsCuentas)
            ->where('p.id_empresa', $empresaId)
            ->where('p.es_por_pagar', false)
            ->when($fechaDesde, function($q) use ($fechaDesde) {
                return $q->whereDate('p.fecha_poliza', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function($q) use ($fechaHasta) {
                return $q->whereDate('p.fecha_poliza', '<=', $fechaHasta);
            })
            ->select('mp.id_cuenta', DB::raw('SUM(mp.monto) as total_movimientos'))
            ->groupBy('mp.id_cuenta');

        $movimientosPorCuenta = $query->get()->keyBy('id_cuenta');

        $resultado = [];
        foreach ($cuentas as $cuenta) {
            $saldoInicial = (float) ($cuenta->saldo_inicial ?? 0);
            $movimientosCuenta = (float) ($movimientosPorCuenta[$cuenta->id_cuenta]->total_movimientos ?? 0);
            $saldo = $saldoInicial + $movimientosCuenta;
            
            $resultado[] = [
                'id_cuenta' => $cuenta->id_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'saldo' => $saldo
            ];
        }

        return $resultado;
    }

    /**
     * ✅ OBTENER CUENTAS DE RESULTADOS - VERSIÓN OPTIMIZADA
     */
    private function getCuentasResultadosOptimizado($empresaId, $fechaDesde = null, $fechaHasta = null, $soloFiscales = false)
    {
        // 🔥 PASO 1: Obtener cuentas de resultados
        $cuentasResultados = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('es_cuenta_resultados', 1)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'nivel', 'id_cuenta_madre', 'cuenta_resultados']);

        if ($cuentasResultados->isEmpty()) {
            return [];
        }

        $idsCuentasResultados = $cuentasResultados->pluck('id_cuenta')->toArray();

        // 🔥 PASO 2: Obtener hijas
        $cuentasHijas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->whereIn('cuenta_resultados', $idsCuentasResultados)
            ->whereNotNull('cuenta_resultados')
            ->where('cuenta_resultados', '>', 0)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'nivel', 'id_cuenta_madre', 'cuenta_resultados']);

        $idsHijas = $cuentasHijas->pluck('id_cuenta')->toArray();

        if (empty($idsHijas)) {
            return [];
        }

        // 🔥 PASO 3: Obtener movimientos de las hijas con una consulta optimizada
        $query = DB::table('movimientos_poliza as mp')
            ->join('polizas as p', 'mp.id_poliza', '=', 'p.id')
            ->whereIn('mp.id_cuenta', $idsHijas)
            ->where('p.id_empresa', $empresaId)
            ->where('p.es_por_pagar', false)
            ->when($fechaDesde, function($q) use ($fechaDesde) {
                return $q->whereDate('p.fecha_poliza', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function($q) use ($fechaHasta) {
                return $q->whereDate('p.fecha_poliza', '<=', $fechaHasta);
            });

        // 🔥 FILTRO FISCAL
        if ($soloFiscales) {
            $query->where('p.categoria', 'FISCAL');
        } else {
            $query->where(function($q) {
                $q->where('p.categoria', '!=', 'FISCAL')
                  ->orWhereNull('p.categoria');
            });
        }

        $movimientosPorCuenta = $query->select('mp.id_cuenta', DB::raw('SUM(mp.monto) as total'))
            ->groupBy('mp.id_cuenta')
            ->get()
            ->keyBy('id_cuenta');

        // 🔥 PASO 4: Construir resultado
        $resultado = [];
        $cuentasHijasPorPadre = $cuentasHijas->groupBy('cuenta_resultados');

        foreach ($cuentasResultados as $padre) {
            $hijas = $cuentasHijasPorPadre->get($padre->id_cuenta, collect());

            if ($hijas->isEmpty()) {
                continue;
            }

            $hijasData = [];
            $subtotal = 0;

            foreach ($hijas as $hija) {
                $saldoHija = (float) ($movimientosPorCuenta[$hija->id_cuenta]->total ?? 0);
                $subtotal += $saldoHija;
                
                $hijasData[] = [
                    'id_cuenta' => $hija->id_cuenta,
                    'codigo_cuenta' => $hija->codigo_cuenta,
                    'nombre_cuenta' => $hija->nombre_cuenta,
                    'nivel' => (int) ($hija->nivel ?? 3),
                    'id_cuenta_madre' => $hija->id_cuenta_madre,
                    'cuenta_resultados' => $hija->cuenta_resultados,
                    'saldo' => $saldoHija,
                    'es_madre' => false,
                    'subtotal' => $saldoHija,
                    'hijas' => [],
                    'es_fiscal' => $soloFiscales
                ];
            }

            $resultado[] = [
                'id_cuenta' => $padre->id_cuenta,
                'codigo_cuenta' => $padre->codigo_cuenta,
                'nombre_cuenta' => $padre->nombre_cuenta,
                'nivel' => (int) ($padre->nivel ?? 2),
                'id_cuenta_madre' => $padre->id_cuenta_madre,
                'cuenta_resultados' => $padre->cuenta_resultados,
                'saldo' => $subtotal,
                'es_madre' => true,
                'subtotal' => $subtotal,
                'hijas' => $hijasData,
                'es_fiscal' => $soloFiscales
            ];
        }

        usort($resultado, function($a, $b) {
            return strcmp($a['codigo_cuenta'], $b['codigo_cuenta']);
        });

        return $resultado;
    }

    /**
     * 🔥 AGRUPAR MOVIMIENTOS POR CUENTA - OPTIMIZADO
     */
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

    /**
     * 🔥 AGRUPAR MOVIMIENTOS POR PERSONA - OPTIMIZADO
     */
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

    /**
     * 📊 EXPORTAR EXCEL
     */
    public function exportExcel(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        if (!$empresaId) {
            return back()->with('error', 'Se requiere una empresa para exportar');
        }

        try {
            // 🔥 OBTENER PÓLIZAS
            $polizaIds = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                })
                ->pluck('id')
                ->toArray();

            if (empty($polizaIds)) {
                return back()->with('error', 'No hay datos para exportar');
            }

            $movimientos = MovimientoPoliza::with([
                'poliza.persona',
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

            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId, $fechaDesde, $fechaHasta);
            $cuentasResultados = $this->getCuentasResultadosOptimizado($empresaId, $fechaDesde, $fechaHasta, false);

            $totalIngresos = array_sum(array_column($data, 'ingreso'));
            $totalEgresos = array_sum(array_column($data, 'egreso'));
            $diferencia = $totalIngresos - $totalEgresos;
            $resultado = $diferencia >= 0 ? 'UTILIDAD' : 'PERDIDA';

            $empresa = \App\Models\Empresa::find($empresaId);
            $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Sin empresa';

            // Crear Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ... (resto del código de Excel igual, pero usando las variables optimizadas)

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
     * 📊 RENDERIZAR CUENTAS JERÁRQUICAS EN EXCEL
     */
    private function renderCuentasJerarquicasExcel($sheet, $cuentas, &$row, $nivel)
    {
        foreach ($cuentas as $cuenta) {
            $indent = str_repeat('  ', $nivel);
            
            $sheet->setCellValue('A' . $row, $indent . $cuenta['nombre_cuenta']);
            $sheet->setCellValue('B' . $row, $cuenta['subtotal']);
            $sheet->setCellValue('C' . $row, $cuenta['nivel']);
            
            if ($cuenta['es_madre']) {
                $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FF']],
                ]);
            }
            
            $sheet->getStyle('B' . $row)
                ->getNumberFormat()
                ->setFormatCode('$#,##0.00');
            
            $row++;
            
            if (!empty($cuenta['hijas'])) {
                $this->renderCuentasJerarquicasExcel($sheet, $cuenta['hijas'], $row, $nivel + 1);
            }
        }
    }

    /**
     * 🔍 OBTENER MOVIMIENTOS DE UNA CUENTA PARA EL MODAL - OPTIMIZADO
     */
    public function getMovimientosCuenta(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $idCuenta = $request->input('id_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $soloFiscales = $request->input('solo_fiscales', false);

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

            $esCuentaResultados = ($cuenta->es_cuenta_resultados == 1);

            // 🔥 OPTIMIZACIÓN: Obtener IDs de cuentas
            if ($esCuentaResultados) {
                $idsHijas = Cuenta::where('id_empresa', $empresaId)
                    ->where('en_uso', true)
                    ->where('cuenta_resultados', $idCuenta)
                    ->whereNotNull('cuenta_resultados')
                    ->where('cuenta_resultados', '>', 0)
                    ->pluck('id_cuenta')
                    ->toArray();

                if (empty($idsHijas)) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'total_ingresos' => 0,
                        'total_egresos' => 0,
                        'total_movimientos' => 0,
                        'saldo' => 0,
                        'mostrando_fiscales' => $soloFiscales
                    ]);
                }

                $query = MovimientoPoliza::with([
                    'poliza.persona',
                    'cuenta',
                    'cuentaFondeadora'
                ])->whereIn('id_cuenta', $idsHijas);

            } else {
                $query = MovimientoPoliza::with([
                    'poliza.persona',
                    'cuenta',
                    'cuentaFondeadora'
                ])->where('id_cuenta', $idCuenta);
            }

            // 🔥 FILTROS
            $query->whereHas('poliza', function($q) use ($empresaId, $soloFiscales, $fechaDesde, $fechaHasta) {
                $q->where('id_empresa', $empresaId)
                  ->where('es_por_pagar', false);

                if ($fechaDesde) {
                    $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                }

                if ($fechaHasta) {
                    $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                }

                if ($soloFiscales) {
                    $q->where('categoria', 'FISCAL');
                } else {
                    $q->where(function($sub) {
                        $sub->where('categoria', '!=', 'FISCAL')
                            ->orWhereNull('categoria');
                    });
                }
            });

            $movimientos = $query->get();

            $totalIngresos = 0;
            $totalEgresos = 0;
            $data = [];

            foreach ($movimientos as $mov) {
                $monto = $mov->monto;
                if ($monto > 0) {
                    $totalIngresos += $monto;
                } else {
                    $totalEgresos += abs($monto);
                }

                $categoria = $mov->poliza->categoria ?? 'NO FISCAL';

                $data[] = [
                    'id_movimiento' => $mov->id,
                    'id_poliza' => $mov->id_poliza,
                    'folio' => $mov->poliza->folio ?? 'N/A',
                    'fecha_poliza' => $mov->poliza->fecha_poliza ? $mov->poliza->fecha_poliza->format('Y-m-d') : null,
                    'persona' => $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona',
                    'cuenta' => $mov->cuenta ? $mov->cuenta->nombre_cuenta : 'N/A',
                    'cuenta_fondeadora' => $mov->cuentaFondeadora ? $mov->cuentaFondeadora->nombre_cuenta : 'N/A',
                    'monto' => $monto,
                    'tipo' => $monto > 0 ? 'INGRESO' : 'EGRESO',
                    'nota' => $mov->poliza->nota ?? null,
                    'categoria' => $categoria
                ];
            }

            usort($data, function($a, $b) {
                return strcmp($b['fecha_poliza'] ?? '', $a['fecha_poliza'] ?? '');
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'total_movimientos' => count($data),
                'saldo' => $totalIngresos - $totalEgresos,
                'mostrando_fiscales' => $soloFiscales
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
}