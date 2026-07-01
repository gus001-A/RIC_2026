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

    public function getMovimientos(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $vista = $request->input('vista', 'por_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere una empresa'
            ], 422);
        }

        try {
            $query = MovimientoPoliza::with([
                'poliza.persona',
                'cuenta',
                'cuentaFondeadora'
            ]);

            $query->whereHas('poliza', function($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            });

            if ($fechaDesde) {
                $query->whereHas('poliza', function($q) use ($fechaDesde) {
                    $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                });
            }

            if ($fechaHasta) {
                $query->whereHas('poliza', function($q) use ($fechaHasta) {
                    $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });
            }

            $movimientos = $query->get();

            if ($vista === 'por_cuenta') {
                $data = $this->agruparPorCuenta($movimientos);
            } else {
                $data = $this->agruparPorPersona($movimientos);
            }

            // Obtener cuentas fondeadoras con saldos
            $fondeadoras = Cuenta::where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->where('fondeo_c', 1)
                ->orderBy('codigo_cuenta')
                ->get()
                ->map(function($cuenta) {
                    return [
                        'id_cuenta' => $cuenta->id_cuenta,
                        'codigo_cuenta' => $cuenta->codigo_cuenta,
                        'nombre_cuenta' => $cuenta->nombre_cuenta,
                        'saldo' => $cuenta->saldo_inicial ?? 0
                    ];
                });

            // Obtener cuentas de resultados con jerarquía
            $cuentasResultados = $this->getCuentasResultadosJerarquicas($empresaId);

            // Calcular utilidad/pérdida
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
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuentas de resultados con jerarquía
     * Busca cuentas de nivel 2 que sean de resultados y calcula su saldo sumando todas sus hijas
     */
    private function getCuentasResultadosJerarquicas($empresaId)
    {
        // ✅ PASO 1: Obtener TODAS las cuentas activas de la empresa
        $todasLasCuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->orderBy('codigo_cuenta')
            ->get();

        if ($todasLasCuentas->isEmpty()) {
            return [];
        }

        // ✅ PASO 2: Construir mapa de cuentas por ID con sus datos
        $cuentasMap = [];
        foreach ($todasLasCuentas as $cuenta) {
            $cuentasMap[$cuenta->id_cuenta] = [
                'id_cuenta' => $cuenta->id_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'saldo' => (float) ($cuenta->saldo_inicial ?? 0),
                'nivel' => (int) ($cuenta->nivel ?? 0),
                'id_cuenta_madre' => $cuenta->id_cuenta_madre,
                'tipo_cuenta' => $cuenta->tipo_cuenta,
                'es_cuenta_resultados' => (int) ($cuenta->es_cuenta_resultados ?? 0),
                'cuenta_resultados' => (int) ($cuenta->cuenta_resultados ?? 0),
                'check_resultados' => $cuenta->check_resultados,
                'es_fiscal' => false,
                'es_madre' => false,
                'subtotal' => (float) ($cuenta->saldo_inicial ?? 0),
                'hijas' => [],
                'nivel_texto' => 'Nivel ' . ($cuenta->nivel ?? 0)
            ];
        }

        // ✅ PASO 3: Identificar cuentas de nivel 2 que son de resultados
        // Condiciones: 
        // - nivel = 2
        // - (tipo_cuenta = 'RESULTADO' OR es_cuenta_resultados = 1 OR cuenta_resultados = 1)
        $cuentasNivel2Resultados = [];
        
        foreach ($cuentasMap as $id => &$cuentaData) {
            // Verificar si es nivel 2 y es de resultados
            $esResultado = ($cuentaData['nivel'] == 2) && (
                $cuentaData['tipo_cuenta'] === 'RESULTADO' ||
                $cuentaData['es_cuenta_resultados'] == 1 ||
                $cuentaData['cuenta_resultados'] == 1 ||
                $cuentaData['check_resultados'] == '1' ||
                $cuentaData['check_resultados'] === 1
            );
            
            if ($esResultado) {
                // Buscar todas las hijas de esta cuenta (recursivo)
                $hijas = $this->getHijasCuenta($id, $cuentasMap);
                
                // Si tiene hijas o no, la incluimos
                if (!empty($hijas)) {
                    $cuentaData['es_madre'] = true;
                    $cuentaData['hijas'] = $hijas;
                    
                    // ✅ Calcular SUBTOTAL = saldo de la cuenta madre + saldo de todas sus hijas
                    $subtotal = $cuentaData['saldo'];
                    foreach ($hijas as $hija) {
                        $subtotal += $hija['saldo'];
                    }
                    $cuentaData['subtotal'] = $subtotal;
                } else {
                    // No tiene hijas, el subtotal es su propio saldo
                    $cuentaData['es_madre'] = false;
                    $cuentaData['subtotal'] = $cuentaData['saldo'];
                }
                
                // Verificar si tiene hijas fiscales
                $cuentaData['es_fiscal'] = $this->tieneHijasFiscales($hijas);
                
                $cuentasNivel2Resultados[] = $cuentaData;
            }
        }

        // ✅ PASO 4: Si no se encontraron cuentas con los campos tradicionales, 
        // buscar todas las cuentas nivel 2 que tengan padre = cuenta "RESULTADOS"
        if (empty($cuentasNivel2Resultados)) {
            // Buscar la cuenta "RESULTADOS"
            $cuentaResultados = Cuenta::where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->where(function($q) {
                    $q->where('nombre_cuenta', 'LIKE', '%RESULTADOS%')
                      ->orWhere('codigo_cuenta', 'LIKE', '%RE%');
                })
                ->where('nivel', 1)
                ->first();

            if ($cuentaResultados) {
                // Buscar todas las cuentas nivel 2 que tengan como madre a "RESULTADOS"
                foreach ($cuentasMap as $id => &$cuentaData) {
                    if ($cuentaData['nivel'] == 2 && $cuentaData['id_cuenta_madre'] == $cuentaResultados->id_cuenta) {
                        $hijas = $this->getHijasCuenta($id, $cuentasMap);
                        
                        if (!empty($hijas)) {
                            $cuentaData['es_madre'] = true;
                            $cuentaData['hijas'] = $hijas;
                            $subtotal = $cuentaData['saldo'];
                            foreach ($hijas as $hija) {
                                $subtotal += $hija['saldo'];
                            }
                            $cuentaData['subtotal'] = $subtotal;
                        } else {
                            $cuentaData['es_madre'] = false;
                            $cuentaData['subtotal'] = $cuentaData['saldo'];
                        }
                        
                        $cuentaData['es_fiscal'] = $this->tieneHijasFiscales($hijas);
                        $cuentasNivel2Resultados[] = $cuentaData;
                    }
                }
            }
        }

        // ✅ PASO 5: Ordenar por código
        usort($cuentasNivel2Resultados, function($a, $b) {
            return strcmp($a['codigo_cuenta'], $b['codigo_cuenta']);
        });

        return $cuentasNivel2Resultados;
    }

    /**
     * Obtener todas las hijas de una cuenta de manera recursiva
     */
    private function getHijasCuenta($idCuentaMadre, &$cuentasMap)
    {
        $hijas = [];
        
        foreach ($cuentasMap as $id => $cuentaData) {
            if ($cuentaData['id_cuenta_madre'] == $idCuentaMadre) {
                // Esta cuenta es hija directa
                $hijaData = $cuentaData;
                
                // Buscar hijas de esta hija (recursivo)
                $subHijas = $this->getHijasCuenta($id, $cuentasMap);
                if (!empty($subHijas)) {
                    $hijaData['hijas'] = $subHijas;
                    $hijaData['es_madre'] = true;
                    // Sumar subtotal de hijas
                    $subtotalHijas = $hijaData['saldo'];
                    foreach ($subHijas as $sh) {
                        $subtotalHijas += $sh['saldo'];
                    }
                    $hijaData['subtotal'] = $subtotalHijas;
                } else {
                    $hijaData['es_madre'] = false;
                    $hijaData['subtotal'] = $hijaData['saldo'];
                }
                
                // Verificar si tiene hijas fiscales
                $hijaData['es_fiscal'] = $this->tieneHijasFiscales($hijaData['hijas'] ?? []);
                
                $hijas[] = $hijaData;
            }
        }
        
        // Ordenar hijas por código
        usort($hijas, function($a, $b) {
            return strcmp($a['codigo_cuenta'], $b['codigo_cuenta']);
        });
        
        return $hijas;
    }

    /**
     * Verificar si alguna hija es fiscal
     */
    private function tieneHijasFiscales($hijas)
    {
        foreach ($hijas as $hija) {
            if (isset($hija['es_fiscal']) && $hija['es_fiscal'] === true) {
                return true;
            }
            if (!empty($hija['hijas'])) {
                if ($this->tieneHijasFiscales($hija['hijas'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function agruparPorCuenta($movimientos)
    {
        $grupos = [];

        foreach ($movimientos as $mov) {
            $idCuenta = $mov->id_cuenta ?? 'sin_cuenta';
            $nombreCuenta = $mov->cuenta ? $mov->cuenta->nombre_cuenta : 'Sin cuenta';
            $codigoCuenta = $mov->cuenta ? $mov->cuenta->codigo_cuenta : '---';
            $persona = $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona';
            $fondeo = $mov->cuentaFondeadora ? $mov->cuentaFondeadora->nombre_cuenta : 'Sin fondeo';

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
            $fondeo = $mov->cuentaFondeadora ? $mov->cuentaFondeadora->nombre_cuenta : 'Sin fondeo';

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
     * Exportar Excel
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
            // Obtener movimientos
            $query = MovimientoPoliza::with([
                'poliza.persona',
                'cuenta',
                'cuentaFondeadora'
            ]);

            $query->whereHas('poliza', function($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            });

            if ($fechaDesde) {
                $query->whereHas('poliza', function($q) use ($fechaDesde) {
                    $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                });
            }

            if ($fechaHasta) {
                $query->whereHas('poliza', function($q) use ($fechaHasta) {
                    $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });
            }

            $movimientos = $query->get();

            if ($vista === 'por_cuenta') {
                $data = $this->agruparPorCuenta($movimientos);
            } else {
                $data = $this->agruparPorPersona($movimientos);
            }

            // Obtener cuentas de resultados con jerarquía
            $cuentasResultados = $this->getCuentasResultadosJerarquicas($empresaId);

            // Calcular totales generales
            $totalIngresos = array_sum(array_column($data, 'ingreso'));
            $totalEgresos = array_sum(array_column($data, 'egreso'));
            $diferencia = $totalIngresos - $totalEgresos;
            $resultado = $diferencia >= 0 ? 'UTILIDAD' : 'PERDIDA';

            $empresa = \App\Models\Empresa::find($empresaId);
            $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Sin empresa';

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ============================================
            // HOJA 1: REPORTE DE MOVIMIENTOS
            // ============================================
            $sheet->setTitle('Movimientos');

            // Título
            $sheet->mergeCells('A1:' . ($vista === 'por_cuenta' ? 'F' : 'D') . '1');
            $sheet->setCellValue('A1', 'REPORTE DE MOVIMIENTOS - ' . ($vista === 'por_cuenta' ? 'POR CUENTA' : 'POR PERSONA'));
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1A3A5C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(35);

            // Subtítulo con fechas y empresa
            $sheet->mergeCells('A2:' . ($vista === 'por_cuenta' ? 'F' : 'D') . '2');
            $fechaTexto = 'Empresa: ' . $nombreEmpresa . ' | ';
            if ($fechaDesde && $fechaHasta) {
                $fechaTexto .= date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta));
            } elseif ($fechaDesde) {
                $fechaTexto .= 'Desde ' . date('d/m/Y', strtotime($fechaDesde));
            } elseif ($fechaHasta) {
                $fechaTexto .= 'Hasta ' . date('d/m/Y', strtotime($fechaHasta));
            } else {
                $fechaTexto .= 'Todas las fechas';
            }
            $sheet->setCellValue('A2', $fechaTexto);
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 11, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Headers
            $row = 4;
            if ($vista === 'por_cuenta') {
                $headers = ['Cuenta', 'Codigo', 'Persona', 'Fondero', 'Ingresos', 'Egresos'];
            } else {
                $headers = ['Persona', 'Fondero', 'Ingresos', 'Egresos'];
            }

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3A5C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ];
            $lastCol = chr(64 + count($headers));
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($headerStyle);
            $sheet->getRowDimension($row)->setRowHeight(25);

            // Datos
            $row++;
            foreach ($data as $item) {
                $col = 'A';
                if ($vista === 'por_cuenta') {
                    $sheet->setCellValue($col . $row, $item['nombre']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['codigo']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['persona']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['fondeo']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['ingreso']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['egreso']);
                } else {
                    $sheet->setCellValue($col . $row, $item['nombre']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['fondeo']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['ingreso']);
                    $col++;
                    $sheet->setCellValue($col . $row, $item['egreso']);
                }
                $row++;
            }

            // Totales
            $totalRow = $row;
            $col = 'A';
            if ($vista === 'por_cuenta') {
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
                $sheet->setCellValue('E' . $totalRow, $totalIngresos);
                $sheet->setCellValue('F' . $totalRow, $totalEgresos);
            } else {
                $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
                $sheet->setCellValue('C' . $totalRow, $totalIngresos);
                $sheet->setCellValue('D' . $totalRow, $totalEgresos);
            }

            $totalStyle = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F0FE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ];
            $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->applyFromArray($totalStyle);

            // Diferencia y resultado
            $diffRow = $totalRow + 2;
            if ($vista === 'por_cuenta') {
                $sheet->mergeCells('A' . $diffRow . ':D' . $diffRow);
                $sheet->setCellValue('A' . $diffRow, 'DIFERENCIA (Ingresos - Egresos)');
                $sheet->setCellValue('E' . $diffRow, $diferencia);
                $sheet->setCellValue('F' . $diffRow, $resultado);
            } else {
                $sheet->mergeCells('A' . $diffRow . ':B' . $diffRow);
                $sheet->setCellValue('A' . $diffRow, 'DIFERENCIA (Ingresos - Egresos)');
                $sheet->setCellValue('C' . $diffRow, $diferencia);
                $sheet->setCellValue('D' . $diffRow, $resultado);
            }

            $diffStyle = [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $diferencia >= 0 ? '10B981' : 'DC2626']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $diferencia >= 0 ? 'ECFDF5' : 'FEF2F2']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ];
            $sheet->getStyle('A' . $diffRow . ':' . $lastCol . $diffRow)->applyFromArray($diffStyle);

            // Formato de moneda
            $moneyColumns = $vista === 'por_cuenta' ? ['E', 'F'] : ['C', 'D'];
            $lastDataRow = $totalRow - 1;
            foreach ($moneyColumns as $col) {
                $sheet->getStyle($col . '5:' . $col . $diffRow)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            }

            // ============================================
            // HOJA 2: CUENTAS DE RESULTADOS (CON JERARQUIA)
            // ============================================
            if (!empty($cuentasResultados)) {
                $sheet2 = $spreadsheet->createSheet();
                $sheet2->setTitle('Cuentas de Resultados');

                // Título
                $sheet2->mergeCells('A1:C1');
                $sheet2->setCellValue('A1', 'CUENTAS DE RESULTADOS');
                $sheet2->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1A3A5C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet2->getRowDimension(1)->setRowHeight(35);

                // Subtítulo
                $sheet2->mergeCells('A2:C2');
                $sheet2->setCellValue('A2', 'Empresa: ' . $nombreEmpresa . ' | ' . $fechaTexto);
                $sheet2->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Headers
                $row = 4;
                $headers2 = ['Cuenta', 'Saldo', 'Nivel'];
                $col = 'A';
                foreach ($headers2 as $header) {
                    $sheet2->setCellValue($col . $row, $header);
                    $sheet2->getColumnDimension($col)->setAutoSize(true);
                    $col++;
                }

                $sheet2->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
                $sheet2->getRowDimension($row)->setRowHeight(25);

                // Datos con jerarquía
                $row++;
                $this->renderCuentasJerarquicasExcel($sheet2, $cuentasResultados, $row, 0);

                // Total general
                $totalRow2 = $row + 1;
                $saldoTotal = 0;
                foreach ($cuentasResultados as $cuenta) {
                    $saldoTotal += $cuenta['subtotal'];
                }

                $sheet2->mergeCells('A' . $totalRow2 . ':B' . $totalRow2);
                $sheet2->setCellValue('A' . $totalRow2, 'TOTAL CUENTAS DE RESULTADOS');
                $sheet2->setCellValue('C' . $totalRow2, $saldoTotal);
                $sheet2->getStyle('A' . $totalRow2 . ':C' . $totalRow2)->applyFromArray($totalStyle);
                $sheet2->getStyle('C' . $totalRow2)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');

                // Resultado
                $resultRow = $totalRow2 + 2;
                $sheet2->mergeCells('A' . $resultRow . ':B' . $resultRow);
                $sheet2->setCellValue('A' . $resultRow, 'RESULTADO DEL PERIODO');
                $sheet2->setCellValue('C' . $resultRow, $diferencia);

                $sheet2->getStyle('A' . $resultRow . ':C' . $resultRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $diferencia >= 0 ? '10B981' : 'DC2626']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $diferencia >= 0 ? 'ECFDF5' : 'FEF2F2']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet2->getStyle('C' . $resultRow)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            }

            // ============================================
            // HOJA 3: CUENTAS FONDEADORAS
            // ============================================
            $fondeadoras = Cuenta::where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->where('fondeo_c', 1)
                ->orderBy('codigo_cuenta')
                ->get();

            if ($fondeadoras->isNotEmpty()) {
                $sheet3 = $spreadsheet->createSheet();
                $sheet3->setTitle('Cuentas Fondeadoras');

                $sheet3->mergeCells('A1:C1');
                $sheet3->setCellValue('A1', 'CUENTAS FONDEADORAS - SALDOS DISPONIBLES');
                $sheet3->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1A3A5C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet3->getRowDimension(1)->setRowHeight(35);

                $sheet3->mergeCells('A2:C2');
                $sheet3->setCellValue('A2', 'Empresa: ' . $nombreEmpresa);
                $sheet3->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $row = 4;
                $headers3 = ['Codigo', 'Nombre de la Cuenta', 'Saldo Disponible'];
                $col = 'A';
                foreach ($headers3 as $header) {
                    $sheet3->setCellValue($col . $row, $header);
                    $sheet3->getColumnDimension($col)->setAutoSize(true);
                    $col++;
                }

                $sheet3->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
                $sheet3->getRowDimension($row)->setRowHeight(25);

                $row++;
                $totalFondeadoras = 0;
                foreach ($fondeadoras as $cuenta) {
                    $saldo = $cuenta->saldo_inicial ?? 0;
                    $totalFondeadoras += $saldo;
                    $sheet3->setCellValue('A' . $row, $cuenta->codigo_cuenta);
                    $sheet3->setCellValue('B' . $row, $cuenta->nombre_cuenta);
                    $sheet3->setCellValue('C' . $row, $saldo);
                    $sheet3->getStyle('C' . $row)
                        ->getNumberFormat()
                        ->setFormatCode('$#,##0.00');
                    $row++;
                }

                $totalRow3 = $row;
                $sheet3->mergeCells('A' . $totalRow3 . ':B' . $totalRow3);
                $sheet3->setCellValue('A' . $totalRow3, 'TOTAL DISPONIBLE');
                $sheet3->setCellValue('C' . $totalRow3, $totalFondeadoras);
                $sheet3->getStyle('A' . $totalRow3 . ':C' . $totalRow3)->applyFromArray($totalStyle);
                $sheet3->getStyle('C' . $totalRow3)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            }

            // Guardar archivo
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
     * Renderizar cuentas jerárquicas en Excel
     */
    private function renderCuentasJerarquicasExcel($sheet, $cuentas, &$row, $nivel)
    {
        foreach ($cuentas as $cuenta) {
            $indent = str_repeat('  ', $nivel);
            
            $sheet->setCellValue('A' . $row, $indent . $cuenta['nombre_cuenta']);
            $sheet->setCellValue('B' . $row, $cuenta['subtotal']);
            $sheet->setCellValue('C' . $row, $cuenta['nivel']);
            
            // Estilo para cuentas madre
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
            
            // Renderizar hijas recursivamente
            if (!empty($cuenta['hijas'])) {
                $this->renderCuentasJerarquicasExcel($sheet, $cuenta['hijas'], $row, $nivel + 1);
            }
        }
    }

    /**
     * Obtener movimientos de una cuenta específica para el modal
     */
    public function getMovimientosCuenta(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $idCuenta = $request->input('id_cuenta');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        if (!$empresaId || !$idCuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros requeridos'
            ], 422);
        }

        try {
            $query = MovimientoPoliza::with([
                'poliza.persona',
                'cuenta',
                'cuentaFondeadora'
            ]);

            $query->where('id_cuenta', $idCuenta)
                ->whereHas('poliza', function($q) use ($empresaId) {
                    $q->where('id_empresa', $empresaId);
                });

            if ($fechaDesde) {
                $query->whereHas('poliza', function($q) use ($fechaDesde) {
                    $q->whereDate('fecha_poliza', '>=', $fechaDesde);
                });
            }

            if ($fechaHasta) {
                $query->whereHas('poliza', function($q) use ($fechaHasta) {
                    $q->whereDate('fecha_poliza', '<=', $fechaHasta);
                });
            }

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

                $data[] = [
                    'id_movimiento' => $mov->id,
                    'id_poliza' => $mov->id_poliza,
                    'folio' => $mov->poliza->folio ?? 'N/A',
                    'fecha_poliza' => $mov->poliza->fecha_poliza ? $mov->poliza->fecha_poliza->format('Y-m-d') : null,
                    'persona' => $mov->poliza->persona ? $mov->poliza->persona->nombre_completo : 'Sin persona',
                    'cuenta' => $mov->cuenta ? $mov->cuenta->nombre_cuenta : 'N/A',
                    'cuenta_fondeadora' => $mov->cuentaFondeadora ? $mov->cuentaFondeadora->nombre_cuenta : 'N/A',
                    'monto' => $monto,
                    'tipo' => $monto > 0 ? 'INGRESO' : 'EGRESO'
                ];
            }

            usort($data, function($a, $b) {
                return strcmp($b['fecha_poliza'], $a['fecha_poliza']);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'total_movimientos' => count($data)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener movimientos de cuenta:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los movimientos: ' . $e->getMessage()
            ], 500);
        }
    }
}