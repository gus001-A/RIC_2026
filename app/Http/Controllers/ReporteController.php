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
        
        // 🔥 INTENTAR OBTENER EMPRESA DE: 1) Request, 2) Sesión
        $empresaId = $request->input('empresa_id');
        
        if (!$empresaId) {
            $empresaId = session('empresa_seleccionada');
        }
        
        // Validar que el usuario tenga acceso a la empresa
        if ($empresaId) {
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $empresaId)
                ->where('id_usuario', $userId)
                ->exists();
                
            if (!$tieneAcceso) {
                $empresaId = null;
            }
        }
        
        // Si aún no hay, usar la primera empresa disponible
        if (!$empresaId && $empresas->count() > 0) {
            $empresaId = $empresas->first()->id;
        }
        
        // 🔥 GUARDAR EN SESIÓN para persistir entre requests
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
            // 🔥 OBTENER PÓLIZAS FILTRADAS
            $polizaQuery = Poliza::where('id_empresa', $empresaId)
                ->where('es_por_pagar', false)
                ->when($fechaDesde, function($q) use ($fechaDesde) {
                    return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                })
                ->when($fechaHasta, function($q) use ($fechaHasta) {
                    return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });

            // 🔥 FILTRO POR CATEGORÍA
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
                    'fondeadoras' => $this->getCuentasFondeadorasConSaldo($empresaId), // 🔥 SIEMPRE DEVOLVER FONDEADORAS
                    'cuentas_resultados' => [],
                    'resultado_utilidad' => 0
                ]);
            }

            // 🔥 OBTENER MOVIMIENTOS DE PÓLIZAS FILTRADAS
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

            // 🔥 OBTENER CUENTAS DE RESULTADOS CON EL FILTRO
            $cuentasResultados = $this->getCuentasResultadosOptimizado(
                $empresaId, 
                $fechaDesde, 
                $fechaHasta, 
                $tipoFiltro
            );

            // 🔥 SIEMPRE OBTENER FONDEADORAS - SIN FILTRO DE FECHAS
            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId);

            $totalIngresos = array_sum(array_column($data, 'ingreso'));
            $totalEgresos = array_sum(array_column($data, 'egreso'));
            $resultadoUtilidad = $totalIngresos - $totalEgresos;

            return response()->json([
                'success' => true,
                'data' => $data,
                'fondeadoras' => $fondeadoras, // 🔥 SIEMPRE VIENEN CON SALDO INICIAL
                'cuentas_resultados' => $cuentasResultados,
                'resultado_utilidad' => $resultadoUtilidad,
                'tipo_filtro' => $tipoFiltro,
                'debug_poliza_ids' => $polizaIds
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
     * 🔥 OBTENER SALDO DE CUENTAS FONDEADORAS - SIEMPRE DEL SALDO_INICIAL
     * ESTE MÉTODO NO DEPENDE DE FECHAS
     */
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
            // 🔥 USAR DIRECTAMENTE EL SALDO_INICIAL COMO SALDO FINAL
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

    /**
     * 🔥 VERSIÓN CORREGIDA - FILTRA CORRECTAMENTE POR PÓLIZAS FISCALES
     */
    private function getCuentasResultadosOptimizado($empresaId, $fechaDesde = null, $fechaHasta = null, $tipoFiltro = 'todas')
    {
        // 🔥 PASO 1: Obtener IDs de pólizas según el filtro
        $polizaQuery = Poliza::where('id_empresa', $empresaId)
            ->where('es_por_pagar', false)
            ->when($fechaDesde, function($q) use ($fechaDesde) {
                return $q->whereDate('fecha_poliza', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function($q) use ($fechaHasta) {
                return $q->whereDate('fecha_poliza', '<=', $fechaHasta);
            });

        // 🔥 APLICAR FILTRO POR CATEGORÍA
        if ($tipoFiltro === 'fiscales') {
            $polizaQuery->where('categoria', 'FISCAL');
        } elseif ($tipoFiltro === 'no_fiscales') {
            $polizaQuery->where('categoria', '!=', 'FISCAL');
        }

        $polizaIds = $polizaQuery->pluck('id')->toArray();

        // 🔥 SI NO HAY PÓLIZAS, RETORNAR VACÍO
        if (empty($polizaIds)) {
            return [];
        }

        // 🔥 PASO 2: Obtener cuentas de resultados
        $cuentasResultados = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('es_cuenta_resultados', 1)
            ->orderBy('codigo_cuenta')
            ->get(['id_cuenta', 'codigo_cuenta', 'nombre_cuenta', 'nivel', 'id_cuenta_madre', 'cuenta_resultados']);

        if ($cuentasResultados->isEmpty()) {
            return [];
        }

        $idsCuentasResultados = $cuentasResultados->pluck('id_cuenta')->toArray();

        // 🔥 PASO 3: Obtener hijas
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

        // 🔥 PASO 4: Obtener movimientos de las hijas SOLO de pólizas filtradas
        $movimientosPorCuenta = DB::table('movimientos_poliza as mp')
            ->whereIn('mp.id_cuenta', $idsHijas)
            ->whereIn('mp.id_poliza', $polizaIds)
            ->select('mp.id_cuenta', DB::raw('SUM(mp.monto) as total'))
            ->groupBy('mp.id_cuenta')
            ->get()
            ->keyBy('id_cuenta');

        // 🔥 PASO 5: Obtener los IDs de las cuentas que TIENEN movimientos
        $cuentasConMovimientos = $movimientosPorCuenta->keys()->toArray();

        // 🔥 PASO 6: Filtrar hijas que tienen movimientos
        $hijasConMovimientos = $cuentasHijas->filter(function($hija) use ($cuentasConMovimientos) {
            return in_array($hija->id_cuenta, $cuentasConMovimientos);
        });

        // 🔥 PASO 7: Construir resultado SOLO con hijas que tienen movimientos
        $resultado = [];
        $cuentasHijasPorPadre = $hijasConMovimientos->groupBy('cuenta_resultados');

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
                    'es_fiscal' => ($tipoFiltro === 'fiscales')
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
                'es_fiscal' => ($tipoFiltro === 'fiscales')
            ];
        }

        usort($resultado, function($a, $b) {
            return strcmp($a['codigo_cuenta'], $b['codigo_cuenta']);
        });

        return $resultado;
    }

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

            $esCuentaResultados = ($cuenta->es_cuenta_resultados == 1);

            if ($esCuentaResultados) {
                $idsHijas = $this->obtenerTodasLasHijas($empresaId, $idCuenta);
                
                if (empty($idsHijas)) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'total_ingresos' => 0,
                        'total_egresos' => 0,
                        'total_movimientos' => 0,
                        'saldo' => 0,
                        'resultado' => 0,
                        'es_cuenta_resultados' => true,
                        'tipo_filtro' => $tipoFiltro
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

            $query->whereHas('poliza', function($q) use ($empresaId, $fechaDesde, $fechaHasta, $tipoFiltro) {
                $q->where('id_empresa', $empresaId)
                  ->where('es_por_pagar', false);

                if ($fechaDesde) {
                    $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                }

                if ($fechaHasta) {
                    $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                }

                if ($tipoFiltro === 'fiscales') {
                    $q->where('categoria', 'FISCAL');
                } elseif ($tipoFiltro === 'no_fiscales') {
                    $q->where('categoria', '!=', 'FISCAL');
                }
            });

            $movimientos = $query->get();

            $totalIngresos = 0;
            $totalEgresos = 0;
            $data = [];

            foreach ($movimientos as $mov) {
                $monto = (float) $mov->monto;
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

            $balance = $totalIngresos - $totalEgresos;

            usort($data, function($a, $b) {
                return strcmp($b['fecha_poliza'] ?? '', $a['fecha_poliza'] ?? '');
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'total_movimientos' => count($data),
                'saldo' => $balance,
                'resultado' => $balance,
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

    private function obtenerTodasLasHijas($empresaId, $idCuentaPadre)
    {
        $idsHijas = [];
        
        $hijasDirectas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('cuenta_resultados', $idCuentaPadre)
            ->whereNotNull('cuenta_resultados')
            ->where('cuenta_resultados', '>', 0)
            ->pluck('id_cuenta')
            ->toArray();

        $idsHijas = array_merge($idsHijas, $hijasDirectas);

        foreach ($hijasDirectas as $idHija) {
            $hijasDeHija = $this->obtenerTodasLasHijas($empresaId, $idHija);
            $idsHijas = array_merge($idsHijas, $hijasDeHija);
        }

        return array_unique($idsHijas);
    }

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

            // 🔥 SIEMPRE OBTENER FONDEADORAS - SIN FILTRO DE FECHAS
            $fondeadoras = $this->getCuentasFondeadorasConSaldo($empresaId);
            $cuentasResultados = $this->getCuentasResultadosOptimizado($empresaId, $fechaDesde, $fechaHasta, $tipoFiltro);

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

            if (!empty($cuentasResultados)) {
                $sheetResultados = $spreadsheet->createSheet();
                $sheetResultados->setTitle('Cuentas de Resultados');
                $rowResultados = 1;
                
                $sheetResultados->setCellValue('A' . $rowResultados, 'CUENTAS DE RESULTADOS');
                $sheetResultados->setCellValue('A' . ($rowResultados + 1), 'Empresa: ' . $nombreEmpresa);
                $sheetResultados->setCellValue('A' . ($rowResultados + 2), 'Filtro: ' . ucfirst($tipoFiltro));
                $sheetResultados->getStyle('A' . $rowResultados . ':A' . ($rowResultados + 2))->getFont()->setBold(true);
                
                $rowResultados += 4;
                
                $sheetResultados->setCellValue('A' . $rowResultados, 'Nombre de la Cuenta');
                $sheetResultados->setCellValue('B' . $rowResultados, 'Saldo');
                $sheetResultados->setCellValue('C' . $rowResultados, 'Nivel');
                
                $headerStyleResultados = [
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ];
                $sheetResultados->getStyle('A' . $rowResultados . ':C' . $rowResultados)->applyFromArray($headerStyleResultados);
                
                $rowResultados++;
                
                $this->renderCuentasJerarquicasExcel($sheetResultados, $cuentasResultados, $rowResultados, 0);
                
                foreach (range('A', 'C') as $col) {
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
}