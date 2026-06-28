<?php

namespace App\Http\Controllers;

use App\Models\MovimientoPoliza;
use App\Models\Poliza;
use App\Models\Cuenta;
use App\Models\TipoIva;
use App\Models\Persona;
use App\Models\Marcador;
use App\Models\AbonoPoliza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class MovimientoController extends Controller
{
    // ============================================
    // 📄 INDEX - LISTADO DE MOVIMIENTOS
    // ============================================
    public function index(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permiso para ver movimientos');
        }

        $empresas = auth()->user()->empresas()->where('activo', true)->get();
        
        if ($empresas->isEmpty()) {
            return Inertia::render('Movimientos/Index', [
                'movimientos' => [
                    'data' => [],
                    'from' => 0,
                    'to' => 0,
                    'total' => 0,
                    'links' => []
                ],
                'empresas' => [],
                'filtros' => [],
                'empresa_seleccionada' => null,
                'saldo_total' => null,
                'vista' => $request->get('vista', 'normal'),
                'contadores' => [
                    'capturados' => 0,
                    'revisados' => 0,
                    'autorizados' => 0,
                    'abonados' => 0,
                    'liquidados' => 0,
                ]
            ]);
        }

        $empresaId = $request->get('empresa_id', $request->session()->get('empresa_movimientos'));
        
        if (!$empresaId || !$empresas->contains('id', $empresaId)) {
            $empresaId = $empresas->first()->id;
        }

        $request->session()->put('empresa_movimientos', $empresaId);

        $vista = $request->get('vista', 'normal');

        $query = MovimientoPoliza::with([
            'poliza.persona',
            'poliza.usuarioCreador',
            'poliza.usuarioRevisor',
            'poliza.usuarioAutorizador',
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
            'poliza.marcador',
            'poliza.abonos'
        ]);

        $query->whereHas('cuenta', function($q) {
            $q->where('en_uso', true);
        });

        $query->whereHas('poliza', function($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId)
              ->whereHas('empresa', function($sub) {
                  $sub->where('activo', true);
              });
        });

        // ✅ FILTRAR POR VISTA
        if ($vista === 'diferidas') {
            $query->whereHas('poliza', function($q) {
                $q->where('es_por_pagar', true);
            });
        } elseif ($vista === 'pendientes') {
            $query->whereHas('poliza', function($q) {
                $q->whereIn('estatus', ['CAPTURADO', 'REVISADO']);
            });
        } elseif ($vista === 'autorizadas') {
            $query->whereHas('poliza', function($q) {
                $q->where('estatus', 'AUTORIZADO');
            });
        } else {
            $query->whereHas('poliza', function($q) {
                $q->where('es_por_pagar', false);
            });
        }

        // 📌 FILTROS
        if ($request->filled('fecha_desde')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->whereDate('fecha_poliza', '>=', $request->fecha_desde);
            });
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->whereDate('fecha_poliza', '<=', $request->fecha_hasta);
            });
        }

        if ($request->filled('referencia')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->where('folio', 'LIKE', '%' . $request->referencia . '%')
                  ->orWhere('referencia', 'LIKE', '%' . $request->referencia . '%');
            });
        }

        if ($request->filled('persona')) {
            $query->whereHas('poliza.persona', function($q) use ($request) {
                $q->where('Nombre', 'LIKE', '%' . $request->persona . '%')
                  ->orWhere('Paterno', 'LIKE', '%' . $request->persona . '%')
                  ->orWhere('Materno', 'LIKE', '%' . $request->persona . '%')
                  ->orWhereRaw("CONCAT(Nombre, ' ', Paterno, ' ', Materno) LIKE ?", ['%' . $request->persona . '%']);
            });
        }

        if ($request->filled('cuenta')) {
            $query->whereHas('cuenta', function($q) use ($request) {
                $q->where('en_uso', true)
                  ->where(function($sub) use ($request) {
                      $sub->where('nombre_cuenta', 'LIKE', '%' . $request->cuenta . '%')
                          ->orWhere('codigo_cuenta', 'LIKE', '%' . $request->cuenta . '%');
                  });
            });
        }

        if ($request->filled('estatus')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->where('estatus', $request->estatus);
            });
        }

        if ($request->filled('tipo_poliza')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->where('tipo_poliza', $request->tipo_poliza);
            });
        }

        // 📌 ORDENAMIENTO
        $sortBy = $request->get('sort_by', 'fecha_poliza');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortMap = [
            'fecha_poliza' => 'polizas.fecha_poliza',
            'fecha_vencimiento' => 'polizas.fecha_vencimiento',
            'referencia' => 'polizas.folio',
            'estatus' => 'polizas.estatus',
            'monto' => 'movimientos_poliza.monto',
            'persona' => 'polizas.id_persona',
            'cuenta' => 'movimientos_poliza.id_cuenta'
        ];

        if (isset($sortMap[$sortBy])) {
            if (strpos($sortMap[$sortBy], '.') !== false) {
                $query->join('polizas', 'movimientos_poliza.id_poliza', '=', 'polizas.id')
                      ->orderBy($sortMap[$sortBy], $sortOrder)
                      ->select('movimientos_poliza.*');
            } else {
                $query->orderBy($sortMap[$sortBy], $sortOrder);
            }
        } else {
            $query->orderBy('movimientos_poliza.created_at', 'desc');
        }

        $perPage = $request->get('per_page', 15);
        $movimientos = $query->paginate($perPage);

        $movimientosData = $movimientos->through(function($movimiento) {
            $abonos = $movimiento->poliza->abonos ?? collect();
            $totalAbonado = $abonos->sum('monto_abonado');
            $saldoPendiente = abs($movimiento->monto) - $totalAbonado;

            $pdfUrl = null;
            if ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) {
                $pdfUrl = route('movimientos.documento.fiscal', [
                    'id' => $movimiento->id_poliza,
                    'tipo' => 'pdf'
                ]);
            }

            return [
                'id_movimiento' => $movimiento->id,
                'id_poliza' => $movimiento->id_poliza,
                'referencia' => $movimiento->poliza->folio ?? null,
                'referencia_adicional' => $movimiento->poliza->referencia ?? null,
                'fecha_poliza' => $movimiento->poliza->fecha_poliza ?? null,
                'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento ?? null,
                'estatus' => $movimiento->poliza->estatus ?? null,
                'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
                'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : null,
                'persona_id' => $movimiento->poliza->id_persona ?? null,
                'cuenta' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->cuenta->nombre_cuenta : null,
                'cuenta_id' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->id_cuenta : null,
                'cuenta_fondeadora' => $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->cuentaFondeadora->nombre_cuenta : null,
                'cuenta_fondeadora_id' => $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->id_caja_fondo : null,
                'nota' => $movimiento->poliza->nota ?? null,
                'monto' => $movimiento->monto,
                'monto_abs' => abs($movimiento->monto),
                'monto_base' => $movimiento->monto_base,
                'monto_iva' => $movimiento->monto_iva,
                'marcador' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : null,
                'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : null,
                'usuario_nombre' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_completo : null,
                'usuario_revisor' => $movimiento->poliza->usuarioRevisor ? $movimiento->poliza->usuarioRevisor->nombre_usuario : null,
                'usuario_autorizador' => $movimiento->poliza->usuarioAutorizador ? $movimiento->poliza->usuarioAutorizador->nombre_usuario : null,
                'fecha_revision' => $movimiento->poliza->fecha_revision ? $movimiento->poliza->fecha_revision->format('d/m/Y H:i') : null,
                'fecha_autorizacion' => $movimiento->poliza->fecha_autorizacion ? $movimiento->poliza->fecha_autorizacion->format('d/m/Y H:i') : null,
                'created_at' => $movimiento->created_at,
                'es_fiscal' => $movimiento->poliza->categoria === 'FISCAL' ? true : false,
                'pdf_url' => $pdfUrl,
                'abonado' => $totalAbonado,
                'saldo_pendiente' => $saldoPendiente,
                'abonos' => $abonos->map(function($abono) {
                    return [
                        'id' => $abono->id,
                        'monto_abonado' => $abono->monto_abonado,
                        'fecha_abono' => $abono->fecha_abono,
                        'referencia' => $abono->referencia,
                        'metodo_pago' => $abono->metodo_pago,
                        'nota' => $abono->nota
                    ];
                }),
                'es_por_pagar' => $movimiento->poliza->es_por_pagar ?? false,
                'tipo_poliza' => $movimiento->poliza->tipo_poliza ?? null,
                'tiene_pdf_fiscal' => !empty($movimiento->poliza->ruta_pdf),
                'tiene_xml_fiscal' => !empty($movimiento->poliza->ruta_xml),
                'comentario_revision' => $movimiento->poliza->comentario_revision,
                'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
                'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
            ];
        });

        $saldoTotal = null;
        if ($vista === 'diferidas') {
            $saldoTotal = $query->get()->sum(function($movimiento) {
                $totalAbonado = AbonoPoliza::where('id_poliza', $movimiento->id_poliza)->sum('monto_abonado');
                return abs($movimiento->monto) - $totalAbonado;
            });
        } else {
            $saldoTotal = (float) $query->sum('monto');
        }

        $filtros = $request->only([
            'fecha_desde', 'fecha_hasta', 'referencia', 
            'estatus', 'tipo_poliza', 'persona', 'cuenta', 'cuenta_fondeadora', 'nota', 'usuario',
            'sort_by', 'sort_order', 'vista', 'mostrar_todos'
        ]);

        // 📊 CONTADORES POR ESTADO
        $contadores = [
            'capturados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'CAPTURADO')->count(),
            'revisados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'REVISADO')->count(),
            'autorizados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'AUTORIZADO')->count(),
            'abonados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'ABONADO')->count(),
            'liquidados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'LIQUIDADO')->count(),
        ];

        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientosData,
            'empresas' => $empresas,
            'filtros' => $filtros,
            'empresa_seleccionada' => (int) $empresaId,
            'saldo_total' => $saldoTotal !== null ? (float) $saldoTotal : null,
            'vista' => $vista,
            'contadores' => $contadores,
        ]);
    }

    // ============================================
    // 📄 CREATE - FORMULARIO DE CREACIÓN
    // ============================================
    public function create()
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para crear movimientos');
        }

        $empresaId = session('empresa_movimientos');
        
        if (!$empresaId) {
            $empresa = auth()->user()->empresas()->first();
            $empresaId = $empresa ? $empresa->id : null;
        }

        if (!$empresaId) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes una empresa seleccionada.');
        }

        $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => $cuenta->saldo_inicial ?? 0
                ];
            });

        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                ];
            });

        $marcadores = Marcador::where('activo', true)
            ->orderBy('nombre_marcador')
            ->get()
            ->map(function($marcador) {
                return [
                    'id' => $marcador->id,
                    'nombre_marcador' => $marcador->nombre_marcador,
                    'descripcion' => $marcador->descripcion
                ];
            });

        $tiposIva = TipoIva::where('activo', true)
            ->orderBy('porcentaje')
            ->get()
            ->map(function($iva) {
                return [
                    'id' => $iva->id,
                    'nombre' => $iva->nombre,
                    'porcentaje' => $iva->porcentaje,
                    'porcentaje_formateado' => $iva->porcentaje . '%'
                ];
            });

        return Inertia::render('Movimientos/Create', [
            'empresa_id' => (int) $empresaId,
            'cuentas' => $cuentas,
            'cuentas_fondeadoras' => $cuentasFondeadoras,
            'tipos_iva' => $tiposIva,
            'marcadores' => $marcadores
        ]);
    }

    // ============================================
    // 📄 CREATE NÓMINA
    // ============================================
    public function createNomina()
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para crear movimientos');
        }

        $empresaId = session('empresa_movimientos');
        
        if (!$empresaId) {
            $empresa = auth()->user()->empresas()->first();
            $empresaId = $empresa ? $empresa->id : null;
        }

        if (!$empresaId) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes una empresa seleccionada.');
        }

        \Log::info('🔍 Buscando personas activas para nómina...');
        
        $empleados = Persona::where('activo', true)
            ->orderBy('Nombre')
            ->orderBy('Paterno')
            ->orderBy('Materno')
            ->get()
            ->map(function($persona) {
                return [
                    'id_persona' => $persona->id_persona,
                    'nombre_completo' => $persona->nombre_completo,
                    'rfc' => $persona->rfc,
                ];
            });

        \Log::info('📊 Empleados encontrados:', ['count' => $empleados->count()]);

        $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => $cuenta->saldo_inicial ?? 0
                ];
            });

        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                ];
            });

        $marcadores = Marcador::where('activo', true)
            ->orderBy('nombre_marcador')
            ->get()
            ->map(function($marcador) {
                return [
                    'id' => $marcador->id,
                    'nombre_marcador' => $marcador->nombre_marcador,
                    'descripcion' => $marcador->descripcion
                ];
            });

        if ($empleados->isEmpty()) {
            return redirect()->route('movimientos.index')
                ->with('info', 'No hay empleados registrados. Primero debes registrar personas.');
        }

        return Inertia::render('Movimientos/NominaCreate', [
            'empresa_id' => (int) $empresaId,
            'empleados' => $empleados,
            'cuentas_fondeadoras' => $cuentasFondeadoras,
            'cuentas' => $cuentas,
            'marcadores' => $marcadores
        ]);
    }

    // ============================================
    // 📄 STORE - GUARDAR PÓLIZA
    // ============================================
    public function store(Request $request)
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para crear movimientos');
        }

        \Log::info('📝 DATOS RECIBIDOS EN STORE:', $request->all());

        $rules = [
            'tipo_poliza' => 'required|in:INGRESO,EGRESO',
            'id_persona' => 'nullable|exists:personas,id_persona',
            'id_cuenta' => 'nullable|exists:cuentas,id_cuenta',
            'es_por_pagar' => 'nullable|in:true,false,1,0,on,off',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
            'es_fiscal' => 'nullable|in:true,false,1,0,on,off',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'total_factura' => 'required|numeric|min:0.01',
            'id_tipo_iva' => 'nullable|exists:tipos_iva,id',
            'fecha_factura' => 'nullable|date',
            'numero_factura' => 'nullable|string|max:50',
            'nota' => 'nullable|string',
            'referencia' => 'nullable|string|max:100',
            'id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'xml_file' => 'nullable|file|mimes:xml,text/xml,application/xml|max:5120',
            'uuid_factura' => 'nullable|string|max:50',
            'serie_factura' => 'nullable|string|max:20',
            'folio_factura' => 'nullable|string|max:20',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('❌ VALIDACIÓN FALLÓ:', $validator->errors()->all());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $empresaId = session('empresa_movimientos');
            
            if (!$empresaId) {
                $empresa = auth()->user()->empresas()->first();
                $empresaId = $empresa ? $empresa->id : null;
            }

            if (!$empresaId) {
                throw new \Exception('No se pudo determinar la empresa para la póliza');
            }

            $esPorPagar = filter_var($request->es_por_pagar, FILTER_VALIDATE_BOOLEAN);
            $esFiscal = filter_var($request->es_fiscal, FILTER_VALIDATE_BOOLEAN);
            $categoria = $esFiscal ? 'FISCAL' : 'NO_FISCAL';

            // 📄 GUARDAR DOCUMENTOS FISCALES
            $rutaPdf = null;
            $nombrePdf = null;
            $rutaXml = null;
            $nombreXml = null;

            if ($esFiscal) {
                if ($request->hasFile('pdf_file')) {
                    try {
                        $pdfFile = $request->file('pdf_file');
                        if ($pdfFile->isValid()) {
                            $nombrePdf = $pdfFile->getClientOriginalName();
                            $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                            \Log::info('📄 PDF guardado:', ['ruta' => $rutaPdf]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('❌ Error al guardar PDF:', ['message' => $e->getMessage()]);
                    }
                }

                if ($request->hasFile('xml_file')) {
                    try {
                        $xmlFile = $request->file('xml_file');
                        if ($xmlFile->isValid()) {
                            $nombreXml = $xmlFile->getClientOriginalName();
                            $rutaXml = $xmlFile->store('documentos_fiscales/xmls', 'public');
                            \Log::info('📄 XML guardado:', ['ruta' => $rutaXml]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('❌ Error al guardar XML:', ['message' => $e->getMessage()]);
                    }
                }
            }

            // ✅ CREAR PÓLIZA CON ESTADO CAPTURADO
            $poliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => $request->tipo_poliza,
                'fecha_poliza' => now(),
                'fecha_vencimiento' => $esPorPagar ? $request->fecha_vencimiento : null,
                'categoria' => $categoria,
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => $esPorPagar,
                'referencia' => $request->referencia,
                'nota' => $request->nota,
                'id_persona' => $request->id_persona,
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'id_usuario_revisor' => null,
                'fecha_autorizacion' => null,
                'fecha_revision' => null,
                'fecha_factura' => $request->fecha_factura,
                'numero_factura' => $request->numero_factura,
                'id_marcador' => $request->id_marcador,
                'fecha_creacion' => now(),
                'ruta_pdf' => $rutaPdf,
                'ruta_xml' => $rutaXml,
                'nombre_pdf' => $nombrePdf,
                'nombre_xml' => $nombreXml,
                'uuid_factura' => $request->uuid_factura,
                'serie_factura' => $request->serie_factura,
                'folio_factura' => $request->folio_factura,
            ]);

            $poliza->refresh();

            // 🔥 CREAR MOVIMIENTO
            $signo = $request->tipo_poliza === 'EGRESO' ? -1 : 1;
            $totalFactura = round($request->total_factura, 2);

            $porcentajeIva = 0;
            $idTipoIva = null;
            
            if ($request->has('id_tipo_iva') && !empty($request->id_tipo_iva)) {
                $idTipoIva = (int) $request->id_tipo_iva;
                $tipoIva = TipoIva::find($idTipoIva);
                if ($tipoIva) {
                    $porcentajeIva = $tipoIva->porcentaje;
                }
            }

            $desglose = $this->calcularDesgloseExacto($totalFactura, $porcentajeIva);
            
            $montoBase = round($desglose['monto_base'] * $signo, 2);
            $montoIva = round($desglose['monto_iva'] * $signo, 2);
            $monto = round(($desglose['monto_base'] + $desglose['monto_iva']) * $signo, 2);

            $movimiento = MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
                'id_tipo_iva' => $idTipoIva,
                'monto' => $monto,
                'monto_base' => $montoBase,
                'monto_iva' => $montoIva
            ]);

            // ✅ ACTUALIZAR SALDOS
            if ($request->id_cuenta_fondeadora) {
                $this->actualizarSaldoCuenta($request->id_cuenta_fondeadora);
            }
            
            if ($request->id_cuenta && $request->id_cuenta != $request->id_cuenta_fondeadora) {
                $this->actualizarSaldoCuenta($request->id_cuenta);
            }

            DB::commit();

            $mensaje = 'Póliza creada exitosamente. Folio: ' . $poliza->folio . ' | Estado: CAPTURADO';
            if ($esFiscal) {
                $mensaje .= ' Documentos fiscales: ';
                if ($rutaPdf) $mensaje .= 'PDF ✓ ';
                if ($rutaXml) $mensaje .= 'XML ✓';
            }

            return redirect()->route('movimientos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ ERROR:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    // ============================================
    // 📄 STORE NÓMINA
    // ============================================
    public function storeNomina(Request $request)
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para crear movimientos');
        }

        $validator = Validator::make($request->all(), [
            'quincena' => 'required|in:1,2',
            'fecha_pago' => 'required|date',
            'id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
            'tipo_poliza' => 'required|in:EGRESO',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'descripcion' => 'nullable|string|max:255',
            'id_cuenta' => 'required|exists:cuentas,id_cuenta',
            'empleados' => 'required|array|min:1',
            'empleados.*.id_persona' => 'required|exists:personas,id_persona',
            'empleados.*.monto' => 'required|numeric|min:0.01',
            'empleados.*.id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
            'empleados.*.observacion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            \Log::error('Validación falló:', $validator->errors()->all());
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $empresaId = session('empresa_movimientos');
            
            if (!$empresaId) {
                $empresa = auth()->user()->empresas()->first();
                $empresaId = $empresa ? $empresa->id : null;
            }

            if (!$empresaId) {
                throw new \Exception('No se pudo determinar la empresa para la póliza');
            }

            $fechaPago = $request->fecha_pago;
            $descripcion = $request->descripcion ?? 'Pago de nómina quincenal';
            $idMarcador = $request->id_marcador;
            $idCuenta = $request->id_cuenta;

            $totalNomina = 0;
            $totalEmpleados = 0;

            foreach ($request->empleados as $empleadoData) {
                $monto = round($empleadoData['monto'], 2);
                $totalNomina += $monto;
                $totalEmpleados++;

                $folio = 'NOM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

                // ✅ PÓLIZA CON ESTADO CAPTURADO
                $poliza = Poliza::create([
                    'id_empresa' => $empresaId,
                    'tipo_poliza' => 'EGRESO',
                    'fecha_poliza' => $fechaPago,
                    'fecha_vencimiento' => null,
                    'categoria' => 'NO_FISCAL',
                    'estatus' => 'CAPTURADO',
                    'es_por_pagar' => false,
                    'referencia' => $folio,
                    'nota' => $descripcion . ' - ' . ($empleadoData['observacion'] ?? 'Sueldo quincenal'),
                    'id_persona' => $empleadoData['id_persona'],
                    'id_usuario_creador' => auth()->id(),
                    'id_usuario_autorizador' => null,
                    'id_usuario_revisor' => null,
                    'fecha_autorizacion' => null,
                    'fecha_revision' => null,
                    'id_marcador' => $idMarcador,
                    'fecha_creacion' => now(),
                ]);

                MovimientoPoliza::create([
                    'id_poliza' => $poliza->id,
                    'id_cuenta' => $idCuenta,
                    'id_caja_fondo' => $empleadoData['id_cuenta_fondeadora'],
                    'id_tipo_iva' => null,
                    'monto' => -$monto,
                    'monto_base' => -$monto,
                    'monto_iva' => 0
                ]);

                $this->actualizarSaldoCuenta($empleadoData['id_cuenta_fondeadora']);
                $this->actualizarSaldoCuenta($idCuenta);

                \Log::info('✅ Póliza de nómina creada:', [
                    'id_poliza' => $poliza->id,
                    'folio' => $poliza->folio,
                    'persona' => $empleadoData['id_persona'],
                    'monto' => $monto,
                    'id_cuenta' => $idCuenta,
                    'id_caja_fondo' => $empleadoData['id_cuenta_fondeadora']
                ]);
            }

            DB::commit();

            return redirect()->route('movimientos.index')
                ->with('success', "Nómina generada exitosamente. {$totalEmpleados} pólizas creadas por un total de $" . number_format($totalNomina, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al generar pólizas de nómina:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Error al generar las pólizas de nómina: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // ✅ REVISAR PÓLIZA (CAPTURADO → REVISADO)
    // ============================================
    public function revisarPoliza(Request $request, $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para revisar pólizas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::findOrFail($id);
            
            if ($poliza->estatus !== 'CAPTURADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado CAPTURADO para ser revisada'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'comentario' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $poliza->estatus = 'REVISADO';
            $poliza->id_usuario_revisor = auth()->id();
            $poliza->fecha_revision = now();
            $poliza->comentario_revision = $request->comentario;
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza revisada exitosamente',
                'data' => [
                    'estatus' => $poliza->estatus,
                    'estatus_texto' => $this->getEstatusTexto($poliza->estatus),
                    'fecha_revision' => $poliza->fecha_revision?->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al revisar póliza:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al revisar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // ✅ AUTORIZAR PÓLIZA (REVISADO → AUTORIZADO)
    // ============================================
    public function autorizarPoliza(Request $request, $id)
    {
        // ✅ Verificar permiso para autorizar
        if (!Gate::allows('autorizar-polizas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para autorizar pólizas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::findOrFail($id);
            
            if ($poliza->estatus !== 'REVISADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado REVISADO para ser autorizada'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'comentario' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $poliza->estatus = 'AUTORIZADO';
            $poliza->id_usuario_autorizador = auth()->id();
            $poliza->fecha_autorizacion = now();
            $poliza->comentario_autorizacion = $request->comentario;
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza autorizada exitosamente',
                'data' => [
                    'estatus' => $poliza->estatus,
                    'estatus_texto' => $this->getEstatusTexto($poliza->estatus),
                    'fecha_autorizacion' => $poliza->fecha_autorizacion?->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al autorizar póliza:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al autorizar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // ❌ RECHAZAR PÓLIZA (REVISADO/AUTORIZADO → CAPTURADO)
    // ============================================
    public function rechazarPoliza(Request $request, $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para rechazar pólizas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::findOrFail($id);
            
            if (!in_array($poliza->estatus, ['REVISADO', 'AUTORIZADO'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado REVISADO o AUTORIZADO para ser rechazada'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|min:10|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $poliza->estatus = 'CAPTURADO';
            $poliza->id_usuario_revisor = null;
            $poliza->fecha_revision = null;
            $poliza->id_usuario_autorizador = null;
            $poliza->fecha_autorizacion = null;
            $poliza->comentario_revision = null;
            $poliza->comentario_autorizacion = null;
            $poliza->motivo_rechazo = $request->motivo;
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza rechazada. Regresa a estado CAPTURADO',
                'data' => [
                    'estatus' => $poliza->estatus,
                    'estatus_texto' => $this->getEstatusTexto($poliza->estatus),
                    'motivo_rechazo' => $poliza->motivo_rechazo
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al rechazar póliza:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 🔄 REVERTIR REVISIÓN (REVISADO → CAPTURADO)
    // ============================================
    public function revertirRevision(Request $request, $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para revertir revisiones'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::findOrFail($id);
            
            if ($poliza->estatus !== 'REVISADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado REVISADO para revertir'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|min:10|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $poliza->estatus = 'CAPTURADO';
            $poliza->id_usuario_revisor = null;
            $poliza->fecha_revision = null;
            $poliza->comentario_revision = null;
            $poliza->motivo_rechazo = $request->motivo;
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revisión revertida. La póliza regresa a estado CAPTURADO',
                'data' => [
                    'estatus' => $poliza->estatus,
                    'estatus_texto' => $this->getEstatusTexto($poliza->estatus),
                    'motivo_rechazo' => $poliza->motivo_rechazo
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al revertir revisión:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al revertir la revisión: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📄 SHOW - VER DETALLE
    // ============================================
    public function show(string $id)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para ver movimientos');
        }

        $movimiento = MovimientoPoliza::with([
            'poliza.persona',
            'poliza.usuarioCreador',
            'poliza.usuarioRevisor',
            'poliza.usuarioAutorizador',
            'poliza.marcador',
            'cuenta',
            'cuentaFondeadora',
            'poliza.abonos'
        ])->find($id);

        if (!$movimiento) {
            return redirect()->route('movimientos.index')
                ->with('error', 'Movimiento no encontrado');
        }

        $abonos = $movimiento->poliza->abonos ?? collect();

        $pdfUrl = null;
        if ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) {
            $pdfUrl = route('movimientos.documento.fiscal', [
                'id' => $movimiento->id_poliza,
                'tipo' => 'pdf'
            ]);
        }

        $movimientoData = [
            'id' => $movimiento->id,
            'id_poliza' => $movimiento->id_poliza,
            'tipo_poliza' => $movimiento->poliza->tipo_poliza,
            'fecha_poliza' => $movimiento->poliza->fecha_poliza,
            'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento,
            'categoria' => $movimiento->poliza->categoria,
            'estatus' => $movimiento->poliza->estatus,
            'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
            'es_por_pagar' => $movimiento->poliza->es_por_pagar,
            'referencia' => $movimiento->poliza->referencia,
            'nota' => $movimiento->poliza->nota,
            'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : null,
            'persona_id' => $movimiento->poliza->id_persona,
            'cuenta' => $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : null,
            'cuenta_id' => $movimiento->id_cuenta,
            'cuenta_fondeadora' => $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : null,
            'cuenta_fondeadora_id' => $movimiento->id_caja_fondo,
            'monto' => $movimiento->monto,
            'monto_abs' => abs($movimiento->monto),
            'monto_base' => $movimiento->monto_base,
            'monto_iva' => $movimiento->monto_iva,
            'marcador' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : null,
            'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : null,
            'usuario_nombre' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_completo : null,
            'usuario_revisor' => $movimiento->poliza->usuarioRevisor ? $movimiento->poliza->usuarioRevisor->nombre_usuario : null,
            'usuario_autorizador' => $movimiento->poliza->usuarioAutorizador ? $movimiento->poliza->usuarioAutorizador->nombre_usuario : null,
            'fecha_revision' => $movimiento->poliza->fecha_revision,
            'fecha_autorizacion' => $movimiento->poliza->fecha_autorizacion,
            'fecha_factura' => $movimiento->poliza->fecha_factura,
            'numero_factura' => $movimiento->poliza->numero_factura,
            'fecha_creacion' => $movimiento->created_at,
            'fecha_actualizacion' => $movimiento->updated_at,
            'pdf_url' => $pdfUrl,
            'tiene_pdf_fiscal' => !empty($movimiento->poliza->ruta_pdf),
            'tiene_xml_fiscal' => !empty($movimiento->poliza->ruta_xml),
            'uuid_factura' => $movimiento->poliza->uuid_factura,
            'serie_factura' => $movimiento->poliza->serie_factura,
            'folio_factura' => $movimiento->poliza->folio_factura,
            'comentario_revision' => $movimiento->poliza->comentario_revision,
            'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
            'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
            'abonos' => $abonos->map(function($abono) {
                return [
                    'id' => $abono->id,
                    'monto_abonado' => $abono->monto_abonado,
                    'fecha_abono' => $abono->fecha_abono,
                    'referencia' => $abono->referencia,
                    'metodo_pago' => $abono->metodo_pago,
                    'nota' => $abono->nota,
                    'usuario' => $abono->usuario?->nombre_usuario ?? 'Sistema'
                ];
            }),
            'total_abonado' => $abonos->sum('monto_abonado'),
            'saldo_pendiente' => abs($movimiento->monto) - $abonos->sum('monto_abonado')
        ];

        return Inertia::render('Movimientos/Show', [
            'movimiento' => $movimientoData
        ]);
    }

    // ============================================
    // 📄 SHOW ABONO
    // ============================================
    public function showAbono(string $id)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('error', 'No tienes permiso para ver movimientos');
        }

        $movimiento = MovimientoPoliza::with([
            'poliza.persona',
            'poliza.usuarioCreador',
            'poliza.usuarioRevisor',
            'poliza.usuarioAutorizador',
            'poliza.marcador',
            'cuenta',
            'cuentaFondeadora',
            'poliza.abonos'
        ])->find($id);

        if (!$movimiento) {
            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('error', 'Movimiento no encontrado');
        }

        if (!$movimiento->poliza->es_por_pagar) {
            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('error', 'Esta póliza no es de tipo "Por pagar"');
        }

        $abonos = $movimiento->poliza->abonos ?? collect();
        $totalAbonado = $abonos->sum('monto_abonado');
        $saldoPendiente = abs($movimiento->monto) - $totalAbonado;

        if ($saldoPendiente <= 0.01) {
            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('info', 'Esta póliza ya está liquidada');
        }

        return Inertia::render('Movimientos/Abono', [
            'movimiento' => [
                'id_movimiento' => $movimiento->id,
                'id_poliza' => $movimiento->id_poliza,
                'referencia' => $movimiento->poliza->folio ?? 'S/N',
                'tipo_poliza' => $movimiento->poliza->tipo_poliza,
                'fecha_poliza' => $movimiento->poliza->fecha_poliza?->format('Y-m-d'),
                'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento?->format('Y-m-d'),
                'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                'cuenta' => $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                'cuenta_fondeadora' => $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : 'N/A',
                'marcador' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : 'Sin marcador',
                'nota' => $movimiento->poliza->nota ?? 'Sin nota',
                'estatus' => $movimiento->poliza->estatus,
                'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
                'monto_abs' => abs($movimiento->monto),
                'monto_base' => abs($movimiento->monto_base ?? 0),
                'monto_iva' => abs($movimiento->monto_iva ?? 0),
                'total_abonado' => $totalAbonado,
                'saldo_pendiente' => $saldoPendiente,
                'abonos' => $abonos->map(function($abono) {
                    return [
                        'id' => $abono->id,
                        'monto_abonado' => $abono->monto_abonado,
                        'fecha_abono' => $abono->fecha_abono,
                        'referencia' => $abono->referencia,
                        'metodo_pago' => $abono->metodo_pago,
                        'nota' => $abono->nota
                    ];
                }),
                'numero_abonos' => $abonos->count(),
                'es_por_pagar' => $movimiento->poliza->es_por_pagar
            ]
        ]);
    }

    // ============================================
    // 📄 EDIT - EDITAR MOVIMIENTO
    // ============================================
    public function edit(string $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para editar movimientos');
        }

        $movimiento = MovimientoPoliza::with([
            'poliza.persona',
            'poliza.usuarioCreador',
            'poliza.usuarioRevisor',
            'poliza.usuarioAutorizador',
            'poliza.marcador',
            'cuenta',
            'cuentaFondeadora'
        ])->find($id);

        if (!$movimiento) {
            return redirect()->route('movimientos.index')
                ->with('error', 'Movimiento no encontrado');
        }

        // Verificar que no esté en un estado que impida editar
        if (in_array($movimiento->poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No se puede editar una póliza en estado ' . $this->getEstatusTexto($movimiento->poliza->estatus));
        }

        $empresaId = session('empresa_movimientos');
        
        if (!$empresaId) {
            $empresa = auth()->user()->empresas()->first();
            $empresaId = $empresa ? $empresa->id : null;
        }

        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                ];
            });

        $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => $cuenta->saldo_inicial ?? 0
                ];
            });

        $marcadores = Marcador::where('activo', true)
            ->orderBy('nombre_marcador')
            ->get()
            ->map(function($marcador) {
                return [
                    'id' => $marcador->id,
                    'nombre_marcador' => $marcador->nombre_marcador,
                    'descripcion' => $marcador->descripcion
                ];
            });

        $tiposIva = TipoIva::where('activo', true)
            ->orderBy('porcentaje')
            ->get()
            ->map(function($iva) {
                return [
                    'id' => $iva->id,
                    'nombre' => $iva->nombre,
                    'porcentaje' => $iva->porcentaje,
                    'porcentaje_formateado' => $iva->porcentaje . '%'
                ];
            });

        $esTraspaso = $movimiento->poliza->tipo_poliza === 'TRASPASO';

        $pdfUrl = null;
        if ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) {
            $pdfUrl = route('movimientos.documento.fiscal', [
                'id' => $movimiento->id_poliza,
                'tipo' => 'pdf'
            ]);
        }

        $movimientoData = [
            'id' => $movimiento->id,
            'id_movimiento' => $movimiento->id,
            'id_poliza' => $movimiento->id_poliza,
            'tipo_poliza' => $movimiento->poliza->tipo_poliza,
            'id_persona' => $movimiento->poliza->id_persona,
            'persona_nombre' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : null,
            'id_cuenta' => $movimiento->id_cuenta,
            'cuenta_nombre' => $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : null,
            'id_cuenta_fondeadora' => $movimiento->id_caja_fondo,
            'cuenta_fondeadora_nombre' => $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : null,
            'es_por_pagar' => (bool) $movimiento->poliza->es_por_pagar,
            'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento?->format('Y-m-d'),
            'es_fiscal' => $movimiento->poliza->categoria === 'FISCAL',
            'id_marcador' => $movimiento->poliza->id_marcador,
            'marcador_nombre' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : null,
            'total_factura' => abs($movimiento->monto),
            'id_tipo_iva' => $movimiento->id_tipo_iva,
            'monto_base' => abs($movimiento->monto_base ?? 0),
            'monto_iva' => abs($movimiento->monto_iva ?? 0),
            'fecha_factura' => $movimiento->poliza->fecha_factura?->format('Y-m-d'),
            'numero_factura' => $movimiento->poliza->numero_factura,
            'nota' => $movimiento->poliza->nota,
            'referencia' => $movimiento->poliza->referencia,
            'folio' => $movimiento->poliza->folio,
            'estatus' => $movimiento->poliza->estatus,
            'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
            'pdf_url' => $pdfUrl,
            'tiene_pdf_fiscal' => !empty($movimiento->poliza->ruta_pdf),
            'tiene_xml_fiscal' => !empty($movimiento->poliza->ruta_xml),
            'uuid_factura' => $movimiento->poliza->uuid_factura,
            'serie_factura' => $movimiento->poliza->serie_factura,
            'folio_factura' => $movimiento->poliza->folio_factura,
            'pdf_existente' => !empty($movimiento->poliza->ruta_pdf),
            'xml_existente' => !empty($movimiento->poliza->ruta_xml),
            'nombre_pdf' => $movimiento->poliza->nombre_pdf,
            'nombre_xml' => $movimiento->poliza->nombre_xml,
            'comentario_revision' => $movimiento->poliza->comentario_revision,
            'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
            'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
        ];

        if ($esTraspaso) {
            $movimientosTraspaso = MovimientoPoliza::where('id_poliza', $movimiento->id_poliza)->get();
            
            $movimientoOrigen = $movimientosTraspaso->firstWhere('monto', '<', 0);
            $movimientoDestino = $movimientosTraspaso->firstWhere('monto', '>', 0);

            $movimientoData['id_cuenta_origen'] = $movimientoOrigen ? $movimientoOrigen->id_cuenta : null;
            $movimientoData['id_cuenta_destino'] = $movimientoDestino ? $movimientoDestino->id_cuenta : null;
            $movimientoData['tipo_poliza'] = 'TRASPASO';
            $movimientoData['monto'] = abs($movimiento->monto);
        }

        return Inertia::render('Movimientos/Edit', [
            'movimiento' => $movimientoData,
            'cuentas' => $cuentas,
            'cuentas_fondeadoras' => $cuentasFondeadoras,
            'tipos_iva' => $tiposIva,
            'marcadores' => $marcadores,
            'es_traspaso' => $esTraspaso
        ]);
    }

    // ============================================
    // 📄 UPDATE - ACTUALIZAR MOVIMIENTO
    // ============================================
    public function update(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para editar movimientos');
        }

        $movimiento = MovimientoPoliza::find($id);

        if (!$movimiento) {
            return redirect()->route('movimientos.index')
                ->with('error', 'Movimiento no encontrado');
        }

        $poliza = Poliza::find($movimiento->id_poliza);
        
        if (!$poliza) {
            return redirect()->route('movimientos.index')
                ->with('error', 'Póliza no encontrada');
        }

        if (in_array($poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
            return back()->with('error', 'No se puede editar una póliza en estado ' . $this->getEstatusTexto($poliza->estatus));
        }

        $validator = Validator::make($request->all(), [
            'id_cuenta' => 'nullable|exists:cuentas,id_cuenta',
            'id_cuenta_fondeadora' => 'nullable|exists:cuentas,id_cuenta',
            'id_tipo_iva' => 'nullable|integer',
            'total_factura' => 'sometimes|numeric|min:0.01',
            'nota' => 'nullable|string',
            'referencia' => 'nullable|string|max:100',
            'id_persona' => 'nullable|exists:personas,id_persona',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'es_por_pagar' => 'boolean',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
            'fecha_factura' => 'nullable|date',
            'numero_factura' => 'nullable|string|max:50',
            'es_fiscal' => 'boolean',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'xml_file' => 'nullable|file|mimes:xml,text/xml,application/xml|max:5120',
            'uuid_factura' => 'nullable|string|max:50',
            'serie_factura' => 'nullable|string|max:20',
            'folio_factura' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $esFiscal = filter_var($request->es_fiscal, FILTER_VALIDATE_BOOLEAN);
            $categoria = $esFiscal ? 'FISCAL' : 'NO_FISCAL';

            $polizaData = [
                'nota' => $request->nota,
                'referencia' => $request->referencia,
                'id_persona' => $request->id_persona,
                'id_marcador' => $request->id_marcador,
                'es_por_pagar' => $request->es_por_pagar ?? false,
                'fecha_vencimiento' => $request->es_por_pagar ? $request->fecha_vencimiento : null,
                'fecha_factura' => $request->fecha_factura,
                'numero_factura' => $request->numero_factura,
                'categoria' => $categoria,
                'uuid_factura' => $request->uuid_factura,
                'serie_factura' => $request->serie_factura,
                'folio_factura' => $request->folio_factura,
            ];

            // 📄 PROCESAR PDF
            if ($request->hasFile('pdf_file')) {
                if ($poliza->ruta_pdf && Storage::disk('public')->exists($poliza->ruta_pdf)) {
                    Storage::disk('public')->delete($poliza->ruta_pdf);
                }
                $pdfFile = $request->file('pdf_file');
                if ($pdfFile->isValid()) {
                    $polizaData['nombre_pdf'] = $pdfFile->getClientOriginalName();
                    $polizaData['ruta_pdf'] = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                }
            }

            // 📄 PROCESAR XML
            if ($request->hasFile('xml_file')) {
                if ($poliza->ruta_xml && Storage::disk('public')->exists($poliza->ruta_xml)) {
                    Storage::disk('public')->delete($poliza->ruta_xml);
                }
                $xmlFile = $request->file('xml_file');
                if ($xmlFile->isValid()) {
                    $polizaData['nombre_xml'] = $xmlFile->getClientOriginalName();
                    $polizaData['ruta_xml'] = $xmlFile->store('documentos_fiscales/xmls', 'public');
                }
            }

            if (!$esFiscal) {
                if ($poliza->ruta_pdf && Storage::disk('public')->exists($poliza->ruta_pdf)) {
                    Storage::disk('public')->delete($poliza->ruta_pdf);
                }
                if ($poliza->ruta_xml && Storage::disk('public')->exists($poliza->ruta_xml)) {
                    Storage::disk('public')->delete($poliza->ruta_xml);
                }
                $polizaData['ruta_pdf'] = null;
                $polizaData['ruta_xml'] = null;
                $polizaData['nombre_pdf'] = null;
                $polizaData['nombre_xml'] = null;
            }

            $poliza->update($polizaData);

            $movimientoData = [
                'id_cuenta' => $request->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
            ];

            if ($request->has('id_tipo_iva')) {
                $movimientoData['id_tipo_iva'] = $request->id_tipo_iva ? (int) $request->id_tipo_iva : null;
            }

            if ($request->has('total_factura') && $request->total_factura > 0) {
                $totalFactura = round($request->total_factura, 2);
                $signo = $poliza->tipo_poliza === 'EGRESO' ? -1 : 1;

                $porcentajeIva = 0;
                if ($request->id_tipo_iva) {
                    $tipoIva = TipoIva::find((int) $request->id_tipo_iva);
                    if ($tipoIva) {
                        $porcentajeIva = $tipoIva->porcentaje;
                    }
                }

                $desglose = $this->calcularDesgloseExacto($totalFactura, $porcentajeIva);
                
                $movimientoData['monto'] = round(($desglose['monto_base'] + $desglose['monto_iva']) * $signo, 2);
                $movimientoData['monto_base'] = round($desglose['monto_base'] * $signo, 2);
                $movimientoData['monto_iva'] = round($desglose['monto_iva'] * $signo, 2);
            }

            $cuentaAnterior = $movimiento->id_cuenta;
            $cajaFondoAnterior = $movimiento->id_caja_fondo;

            $movimiento->update($movimientoData);
            $this->actualizarEstatusPoliza($poliza);

            if ($cajaFondoAnterior) {
                $this->actualizarSaldoCuenta($cajaFondoAnterior);
            }
            if ($cuentaAnterior && $cuentaAnterior != $cajaFondoAnterior) {
                $this->actualizarSaldoCuenta($cuentaAnterior);
            }

            if ($movimiento->id_caja_fondo) {
                $this->actualizarSaldoCuenta($movimiento->id_caja_fondo);
            }
            if ($movimiento->id_cuenta && $movimiento->id_cuenta != $movimiento->id_caja_fondo) {
                $this->actualizarSaldoCuenta($movimiento->id_cuenta);
            }

            DB::commit();

            return redirect()->route('movimientos.index')
                ->with('success', 'Movimiento actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar movimiento:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    // ============================================
    // 📄 DESTROY - ELIMINAR MOVIMIENTO
    // ============================================
    public function destroy(string $id)
    {
        // ✅ Verificar permiso para eliminar movimientos
        if (!Gate::allows('eliminar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar movimientos'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $movimiento = MovimientoPoliza::with('poliza')->find($id);

            if (!$movimiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ], 404);
            }

            $poliza = $movimiento->poliza;
            
            if (in_array($poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar movimientos de una póliza en estado ' . $this->getEstatusTexto($poliza->estatus)
                ], 422);
            }

            $idCuenta = $movimiento->id_cuenta;
            $idCajaFondo = $movimiento->id_caja_fondo;

            AbonoPoliza::where('id_poliza', $poliza->id)->delete();

            if ($poliza->ruta_pdf && Storage::disk('public')->exists($poliza->ruta_pdf)) {
                Storage::disk('public')->delete($poliza->ruta_pdf);
            }
            if ($poliza->ruta_xml && Storage::disk('public')->exists($poliza->ruta_xml)) {
                Storage::disk('public')->delete($poliza->ruta_xml);
            }

            $movimiento->delete();

            if ($idCajaFondo) {
                $this->actualizarSaldoCuenta($idCajaFondo);
            }
            if ($idCuenta && $idCuenta != $idCajaFondo) {
                $this->actualizarSaldoCuenta($idCuenta);
            }

            $movimientosRestantes = MovimientoPoliza::where('id_poliza', $poliza->id)->count();
            if ($movimientosRestantes === 0) {
                $poliza->delete();
            } else {
                $this->actualizarEstatusPoliza($poliza);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar movimiento:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📄 STORE ABONO
    // ============================================
    public function storeAbono(Request $request)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return back()->with('error', 'No tienes permiso para registrar abonos');
        }

        $validator = Validator::make($request->all(), [
            'id_poliza' => 'required|exists:polizas,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_abono' => 'required|date',
            'referencia' => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|string|max:50',
            'nota' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::find($request->id_poliza);
            if (!$poliza) {
                return back()->with('error', 'Póliza no encontrada');
            }

            if (in_array($poliza->estatus, ['LIQUIDADO'])) {
                return back()->with('error', 'No se pueden agregar abonos a una póliza liquidada');
            }

            $movimiento = MovimientoPoliza::where('id_poliza', $poliza->id)->first();
            if (!$movimiento) {
                return back()->with('error', 'Movimiento no encontrado');
            }

            $totalAbonado = AbonoPoliza::where('id_poliza', $poliza->id)->sum('monto_abonado');
            $montoTotal = abs($movimiento->monto);
            $saldoPendiente = round($montoTotal - $totalAbonado, 2);
            $montoAbonado = round($request->monto_abonado, 2);

            if ($montoAbonado > $saldoPendiente) {
                return back()->with('error', 'El monto abonado no puede ser mayor al saldo pendiente ($' . number_format($saldoPendiente, 2) . ')');
            }

            $abono = AbonoPoliza::create([
                'id_poliza' => $request->id_poliza,
                'monto_abonado' => $montoAbonado,
                'fecha_abono' => $request->fecha_abono,
                'referencia' => $request->referencia,
                'metodo_pago' => $request->metodo_pago ?? 'TRANSFERENCIA',
                'nota' => $request->nota,
                'id_usuario' => auth()->id()
            ]);

            $this->actualizarEstatusPoliza($poliza);

            DB::commit();

            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('success', 'Abono registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar abono:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al registrar el abono: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // 📄 DESTROY ABONO
    // ============================================
    public function destroyAbono(string $id)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar abonos'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $abono = AbonoPoliza::find($id);
            
            if (!$abono) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abono no encontrado'
                ], 404);
            }

            $poliza = Poliza::find($abono->id_poliza);
            if (!$poliza) {
                return response()->json([
                    'success' => false,
                    'message' => 'Póliza no encontrada'
                ], 404);
            }

            if ($poliza->estatus === 'LIQUIDADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar abonos de una póliza liquidada'
                ], 422);
            }

            $abono->delete();
            $this->actualizarEstatusPoliza($poliza);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Abono eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el abono: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 💰 LIQUIDAR PÓLIZA
    // ============================================
    public function liquidarPoliza(Request $request)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para liquidar pólizas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $movimiento = MovimientoPoliza::with('poliza')->find($request->id_movimiento);
            if (!$movimiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ], 404);
            }

            $poliza = $movimiento->poliza;
            if ($poliza->estatus === 'LIQUIDADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está liquidada'
                ], 422);
            }

            if ($poliza->estatus !== 'ABONADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado ABONADO para liquidar'
                ], 422);
            }

            $cuentaFondeadora = Cuenta::find($request->id_cuenta_fondeadora);
            if (!$cuentaFondeadora) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta fondeadora no encontrada'
                ], 404);
            }

            $saldoActual = $cuentaFondeadora->saldo_inicial ?? 0;
            $montoLiquidar = abs($movimiento->monto);
            $totalAbonado = AbonoPoliza::where('id_poliza', $poliza->id)->sum('monto_abonado');
            $saldoPendiente = $montoLiquidar - $totalAbonado;

            if ($saldoPendiente <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está completamente pagada'
                ], 422);
            }

            if ($saldoActual < $saldoPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => "Saldo insuficiente en la cuenta fondeadora. Disponible: $" . number_format($saldoActual, 2) . ", Necesario: $" . number_format($saldoPendiente, 2)
                ], 422);
            }

            $abono = AbonoPoliza::create([
                'id_poliza' => $poliza->id,
                'monto_abonado' => $saldoPendiente,
                'fecha_abono' => now()->toDateString(),
                'referencia' => 'LIQUIDACION AUTOMATICA - ' . ($request->referencia ?? ''),
                'metodo_pago' => 'TRANSFERENCIA',
                'nota' => 'Liquidación total de póliza desde el módulo de movimientos. Cuenta fondeadora: ' . $cuentaFondeadora->nombre_cuenta,
                'id_usuario' => auth()->id()
            ]);

            $cuentaFondeadora->saldo_inicial = $saldoActual - $saldoPendiente;
            $cuentaFondeadora->save();

            $poliza->estatus = 'LIQUIDADO';
            $poliza->fecha_liquidacion = now();
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza liquidada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al liquidar póliza:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al liquidar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 💰 LIQUIDAR INGRESO
    // ============================================
    public function liquidarIngreso(Request $request)
    {
        // ✅ Verificar permiso para editar movimientos
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para liquidar ingresos'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $movimiento = MovimientoPoliza::with('poliza')->find($request->id_movimiento);
            if (!$movimiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ], 404);
            }

            $poliza = $movimiento->poliza;
            if ($poliza->estatus === 'LIQUIDADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está liquidada'
                ], 422);
            }

            if ($poliza->tipo_poliza !== 'INGRESO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta función solo aplica para pólizas de INGRESO'
                ], 422);
            }

            if ($poliza->estatus !== 'AUTORIZADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado AUTORIZADO para liquidar'
                ], 422);
            }

            $montoLiquidar = abs($movimiento->monto);
            $totalAbonado = AbonoPoliza::where('id_poliza', $poliza->id)->sum('monto_abonado');
            $saldoPendiente = $montoLiquidar - $totalAbonado;

            if ($saldoPendiente <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está completamente pagada'
                ], 422);
            }

            $abono = AbonoPoliza::create([
                'id_poliza' => $poliza->id,
                'monto_abonado' => $saldoPendiente,
                'fecha_abono' => now()->toDateString(),
                'referencia' => 'LIQUIDACION INGRESO - ' . ($request->referencia ?? ''),
                'metodo_pago' => 'TRANSFERENCIA',
                'nota' => 'Liquidación total de póliza de ingreso desde el módulo de movimientos.',
                'id_usuario' => auth()->id()
            ]);

            // Cambiar a ABONADO primero y luego LIQUIDADO
            $poliza->estatus = 'ABONADO';
            $poliza->fecha_abono = now();
            $poliza->save();

            $poliza->estatus = 'LIQUIDADO';
            $poliza->fecha_liquidacion = now();
            $poliza->save();

            if ($movimiento->id_cuenta) {
                $this->actualizarSaldoCuenta($movimiento->id_cuenta);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza de ingreso liquidada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al liquidar póliza de ingreso:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al liquidar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 🔄 STORE TRASPASO
    // ============================================
    public function storeTraspaso(Request $request)
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return back()->with('error', 'No tienes permiso para crear traspasos');
        }

        $validator = Validator::make($request->all(), [
            'tipo_poliza' => 'required|in:TRASPASO',
            'id_cuenta_origen' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_destino',
            'id_cuenta_destino' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_origen',
            'monto' => 'required|numeric|min:0.01',
            'es_fiscal' => 'boolean',
            'es_por_pagar' => 'boolean',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'referencia' => 'nullable|string|max:100',
            'nota' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $empresaId = session('empresa_movimientos');
            
            if (!$empresaId) {
                $empresa = auth()->user()->empresas()->first();
                $empresaId = $empresa ? $empresa->id : null;
            }

            if (!$empresaId) {
                throw new \Exception('No se pudo determinar la empresa');
            }

            $categoria = $request->es_fiscal ? 'FISCAL' : 'NO_FISCAL';

            $poliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => 'TRASPASO',
                'fecha_poliza' => now(),
                'fecha_vencimiento' => $request->es_por_pagar ? $request->fecha_vencimiento : null,
                'categoria' => $categoria,
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => $request->es_por_pagar ?? false,
                'referencia' => $request->referencia,
                'nota' => $request->nota,
                'id_persona' => null,
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'id_usuario_revisor' => null,
                'fecha_autorizacion' => null,
                'fecha_revision' => null,
                'id_marcador' => $request->id_marcador,
                'fecha_creacion' => now(),
            ]);

            $monto = round($request->monto, 2);

            MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta_origen,
                'id_caja_fondo' => null,
                'id_tipo_iva' => null,
                'monto' => -$monto,
                'monto_base' => -$monto,
                'monto_iva' => 0
            ]);

            MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta_destino,
                'id_caja_fondo' => null,
                'id_tipo_iva' => null,
                'monto' => $monto,
                'monto_base' => $monto,
                'monto_iva' => 0
            ]);

            $this->actualizarSaldoCuenta($request->id_cuenta_origen);
            $this->actualizarSaldoCuenta($request->id_cuenta_destino);

            DB::commit();

            return redirect()->route('movimientos.index')
                ->with('success', 'Traspaso creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear traspaso:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al crear el traspaso: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // 🔍 BUSCAR PERSONAS
    // ============================================
    public function buscarPersonas(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json([], 403);
        }

        $search = $request->get('q', '');

        $personas = Persona::where(function($query) use ($search) {
                $query->where('Nombre', 'LIKE', "%{$search}%")
                      ->orWhere('Paterno', 'LIKE', "%{$search}%")
                      ->orWhere('Materno', 'LIKE', "%{$search}%")
                      ->orWhereRaw("CONCAT(Nombre, ' ', Paterno, ' ', Materno) LIKE ?", ["%{$search}%"]);
            })
            ->where('activo', 1)
            ->limit(10)
            ->get()
            ->map(function($persona) {
                return [
                    'id_persona' => $persona->id_persona,
                    'nombre_completo' => $persona->nombre_completo,
                    'rfc' => $persona->rfc
                ];
            });

        return response()->json($personas);
    }

    // ============================================
    // 🔍 BUSCAR CUENTAS
    // ============================================
    public function buscarCuentas(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json([], 403);
        }

        $search = $request->get('q', '');
        $empresaId = session('empresa_movimientos');

        $cuentas = Cuenta::where(function($query) use ($search) {
                $query->where('nombre_cuenta', 'LIKE', "%{$search}%")
                      ->orWhere('codigo_cuenta', 'LIKE', "%{$search}%");
            })
            ->where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->limit(10)
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                ];
            });

        return response()->json($cuentas);
    }

    // ============================================
    // 📄 OBTENER DOCUMENTO FISCAL (PDF o XML)
    // ============================================
    public function getDocumentoFiscal($id, $tipo)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            abort(403, 'No tienes permiso para ver documentos fiscales');
        }

        $poliza = Poliza::find($id);
        
        if (!$poliza) {
            abort(404, 'Póliza no encontrada');
        }

        if ($poliza->categoria !== 'FISCAL') {
            abort(404, 'Esta póliza no es fiscal');
        }

        $ruta = $tipo === 'pdf' ? $poliza->ruta_pdf : $poliza->ruta_xml;
        $nombre = $tipo === 'pdf' ? $poliza->nombre_pdf : $poliza->nombre_xml;

        if (empty($ruta) || !Storage::disk('public')->exists($ruta)) {
            abort(404, 'Documento no encontrado');
        }

        return Storage::disk('public')->response($ruta, $nombre ?? ($tipo === 'pdf' ? 'factura.pdf' : 'factura.xml'));
    }

    // ============================================
    // 🧮 CALCULAR DESGLOSE DE IVA (API)
    // ============================================
    public function calcularDesglose(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json(['error' => 'No tienes permiso'], 403);
        }

        $totalFactura = round($request->get('total_factura', 0), 2);
        $idTipoIva = (int) $request->get('id_tipo_iva', 0);

        $porcentajeIva = 0;
        $nombreIva = 'Sin IVA';

        if ($idTipoIva > 0 && $totalFactura > 0) {
            $tipoIva = TipoIva::find($idTipoIva);
            if ($tipoIva) {
                $porcentajeIva = $tipoIva->porcentaje;
                $nombreIva = $tipoIva->nombre;
            }
        }

        $desglose = $this->calcularDesgloseExacto($totalFactura, $porcentajeIva);

        return response()->json([
            'monto_base' => $desglose['monto_base'],
            'monto_iva' => $desglose['monto_iva'],
            'total_factura' => $desglose['total'],
            'porcentaje_iva' => $porcentajeIva,
            'nombre_iva' => $nombreIva
        ]);
    }

    // ============================================
    // 📝 OBTENER EMPLEADOS PARA NÓMINA (API)
    // ============================================
    public function getEmpleadosNomina(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json([], 403);
        }

        $search = $request->get('q', '');

        $empleados = Persona::where('activo', true)
            ->where(function($query) use ($search) {
                if (!empty($search)) {
                    $query->where('Nombre', 'LIKE', "%{$search}%")
                          ->orWhere('Paterno', 'LIKE', "%{$search}%")
                          ->orWhere('Materno', 'LIKE', "%{$search}%")
                          ->orWhereRaw("CONCAT(Nombre, ' ', Paterno, ' ', Materno) LIKE ?", ["%{$search}%"]);
                }
            })
            ->orderBy('Nombre')
            ->orderBy('Paterno')
            ->orderBy('Materno')
            ->limit(20)
            ->get()
            ->map(function($persona) {
                return [
                    'id_persona' => $persona->id_persona,
                    'nombre_completo' => $persona->nombre_completo,
                    'rfc' => $persona->rfc,
                ];
            });

        return response()->json($empleados);
    }

    // ============================================
    // 📝 CALCULAR FECHA DE PAGO POR QUINCENA (API)
    // ============================================
    public function calcularFechaPago(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json(['error' => 'No tienes permiso'], 403);
        }

        $quincena = $request->get('quincena');
        $fechaReferencia = $request->get('fecha', now()->toDateString());

        if (!$quincena) {
            return response()->json(['fecha_pago' => null]);
        }

        $fecha = new \DateTime($fechaReferencia);
        $year = (int) $fecha->format('Y');
        $month = (int) $fecha->format('m');
        $day = (int) $fecha->format('d');

        $fechaPago = null;

        if ($quincena == '1') {
            $fechaPago = new \DateTime("{$year}-{$month}-15");
            if ($day > 15) {
                $fechaPago->modify('+1 month');
            }
        } else {
            $ultimoDia = (int) (new \DateTime("{$year}-{$month}-01"))->format('t');
            $fechaPago = new \DateTime("{$year}-{$month}-{$ultimoDia}");
            if ($day > $ultimoDia) {
                $fechaPago->modify('+1 month');
                $ultimoDia = (int) (new \DateTime($fechaPago->format('Y-m-01')))->format('t');
                $fechaPago = new \DateTime($fechaPago->format('Y-m-') . $ultimoDia);
            }
        }

        return response()->json([
            'fecha_pago' => $fechaPago->format('Y-m-d'),
            'quincena' => $quincena
        ]);
    }

    // ============================================
    // 🏷️ STORE MARCADOR
    // ============================================
    public function storeMarcador(Request $request)
    {
        // ✅ Verificar permiso para crear movimientos
        if (!Gate::allows('crear-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear marcadores'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre_marcador' => 'required|string|max:100|unique:marcadores,nombre_marcador',
            'descripcion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $marcador = Marcador::create([
                'nombre_marcador' => strtoupper($request->nombre_marcador),
                'descripcion' => $request->descripcion,
                'activo' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marcador creado exitosamente',
                'data' => [
                    'id' => $marcador->id,
                    'nombre_marcador' => $marcador->nombre_marcador,
                    'descripcion' => $marcador->descripcion,
                    'activo' => $marcador->activo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el marcador: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 💰 OBTENER SALDO DE CUENTA FONDEADORA
    // ============================================
    public function getSaldoCuentaFondeadora(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso'
            ], 403);
        }

        $idCuenta = $request->get('id_cuenta');
        
        if (!$idCuenta) {
            return response()->json([
                'success' => false,
                'message' => 'ID de cuenta no proporcionado'
            ], 422);
        }

        $cuenta = Cuenta::find($idCuenta);
        
        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'saldo' => $cuenta->saldo_inicial ?? 0,
                'saldo_formateado' => '$' . number_format($cuenta->saldo_inicial ?? 0, 2)
            ]
        ]);
    }

    // ============================================
    // 💰 OBTENER CUENTAS FONDEADORAS
    // ============================================
    public function getCuentasFondeadoras(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json(['data' => []]);
        }

        $empresaId = $request->get('empresa_id', session('empresa_movimientos'));
        
        if (!$empresaId) {
            return response()->json(['data' => []]);
        }
        
        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => $cuenta->saldo_inicial ?? 0
                ];
            });
        
        return response()->json(['data' => $cuentas]);
    }

    // ============================================
    // 💰 OBTENER SALDO DE CUENTA
    // ============================================
    public function obtenerSaldoCuenta(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return response()->json([
                'saldo' => 0,
                'error' => 'No tienes permiso'
            ], 403);
        }

        $cuenta = Cuenta::find($request->id);

        if (!$cuenta) {
            return response()->json([
                'saldo' => 0,
                'error' => 'Cuenta no encontrada'
            ]);
        }

        return response()->json([
            'saldo' => $cuenta->saldo_inicial ?? 0,
            'cuenta' => $cuenta->nombre_cuenta,
            'id_cuenta' => $cuenta->id_cuenta
        ]);
    }

    // ============================================
    // 📊 EXPORTAR EXCEL
    // ============================================
    public function exportExcel(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return redirect()->back()->with('error', 'No tienes permiso para exportar movimientos');
        }

        try {
            $empresaId = $request->get('empresa_id', session('empresa_movimientos'));
            $vista = $request->get('vista', 'normal');

            if (!$empresaId) {
                return redirect()->back()->with('error', 'No se ha seleccionado una empresa');
            }

            $query = MovimientoPoliza::with([
                'poliza.persona',
                'poliza.usuarioCreador',
                'poliza.usuarioRevisor',
                'poliza.usuarioAutorizador',
                'cuenta',
                'cuentaFondeadora',
                'poliza.marcador',
                'poliza.abonos'
            ]);

            $query->whereHas('poliza', function($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            });

            if ($vista === 'diferidas') {
                $query->whereHas('poliza', function($q) {
                    $q->where('es_por_pagar', true);
                });
            } elseif ($vista === 'pendientes') {
                $query->whereHas('poliza', function($q) {
                    $q->whereIn('estatus', ['CAPTURADO', 'REVISADO']);
                });
            } elseif ($vista === 'autorizadas') {
                $query->whereHas('poliza', function($q) {
                    $q->where('estatus', 'AUTORIZADO');
                });
            } else {
                $query->whereHas('poliza', function($q) {
                    $q->where('es_por_pagar', false);
                });
            }

            if ($request->filled('fecha_desde')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->whereDate('fecha_poliza', '>=', $request->fecha_desde);
                });
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->whereDate('fecha_poliza', '<=', $request->fecha_hasta);
                });
            }

            if ($request->filled('referencia')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->where('folio', 'LIKE', '%' . $request->referencia . '%')
                      ->orWhere('referencia', 'LIKE', '%' . $request->referencia . '%');
                });
            }

            $movimientos = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            if ($vista === 'diferidas') {
                $headers = ['Vencimiento', 'Persona', 'Nota', 'Monto', 'Abonado', 'Saldo Pendiente', 'Usuario', 'Estatus'];
            } else {
                $headers = ['Referencia', 'Fecha Póliza', 'Tipo', 'Estatus', 'Persona', 'Cuenta', 'Cta. Fondeo', 'Nota', 'Monto', 'Usuario', 'PDF Fiscal'];
            }

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3A5C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }

            $lastCol = chr(64 + count($headers));
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);

            $row = 2;
            foreach ($movimientos as $movimiento) {
                $abonos = $movimiento->poliza->abonos ?? collect();
                $totalAbonado = $abonos->sum('monto_abonado');
                $saldoPendiente = abs($movimiento->monto) - $totalAbonado;

                if ($vista === 'diferidas') {
                    $data = [
                        $this->formatFechaExcel($movimiento->poliza->fecha_vencimiento),
                        $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                        $movimiento->poliza->nota ?? '—',
                        $movimiento->monto,
                        $totalAbonado,
                        $saldoPendiente,
                        $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : 'Sistema',
                        $this->getEstatusTexto($movimiento->poliza->estatus),
                    ];
                } else {
                    $tienePdf = ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) ? 'Sí' : 'No';
                    $data = [
                        $movimiento->poliza->folio ?? '—',
                        $this->formatFechaExcel($movimiento->poliza->fecha_poliza),
                        $movimiento->poliza->tipo_poliza ?? '—',
                        $this->getEstatusTexto($movimiento->poliza->estatus),
                        $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                        $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                        $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : 'N/A',
                        $movimiento->poliza->nota ?? '—',
                        $movimiento->monto,
                        $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : 'Sistema',
                        $tienePdf
                    ];
                }

                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            $lastRow = $row - 1;
            $sheet->getStyle('A1:' . $lastCol . $lastRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);

            if ($vista === 'diferidas') {
                $moneyColumns = ['D', 'E', 'F'];
            } else {
                $moneyColumns = ['I'];
            }

            foreach ($moneyColumns as $col) {
                $sheet->getStyle($col . '2:' . $col . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'movimientos_' . date('Y-m-d_H-i-s') . '.xlsx';

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
            return redirect()->back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    // ============================================
    // 📄 EXPORTAR PDF
    // ============================================
    public function exportPdf(Request $request)
    {
        // ✅ Verificar permiso para ver movimientos
        if (!Gate::allows('ver-movimientos')) {
            return redirect()->back()->with('error', 'No tienes permiso para exportar movimientos');
        }

        try {
            $empresaId = $request->get('empresa_id', session('empresa_movimientos'));
            $vista = $request->get('vista', 'normal');

            if (!$empresaId) {
                return redirect()->back()->with('error', 'No se ha seleccionado una empresa');
            }

            $query = MovimientoPoliza::with([
                'poliza.persona',
                'poliza.usuarioCreador',
                'poliza.usuarioRevisor',
                'poliza.usuarioAutorizador',
                'cuenta',
                'cuentaFondeadora',
                'poliza.marcador',
                'poliza.abonos'
            ]);

            $query->whereHas('poliza', function($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            });

            if ($vista === 'diferidas') {
                $query->whereHas('poliza', function($q) {
                    $q->where('es_por_pagar', true);
                });
            } elseif ($vista === 'pendientes') {
                $query->whereHas('poliza', function($q) {
                    $q->whereIn('estatus', ['CAPTURADO', 'REVISADO']);
                });
            } elseif ($vista === 'autorizadas') {
                $query->whereHas('poliza', function($q) {
                    $q->where('estatus', 'AUTORIZADO');
                });
            } else {
                $query->whereHas('poliza', function($q) {
                    $q->where('es_por_pagar', false);
                });
            }

            if ($request->filled('fecha_desde')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->whereDate('fecha_poliza', '>=', $request->fecha_desde);
                });
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->whereDate('fecha_poliza', '<=', $request->fecha_hasta);
                });
            }

            if ($request->filled('referencia')) {
                $query->whereHas('poliza', function($q) use ($request) {
                    $q->where('folio', 'LIKE', '%' . $request->referencia . '%')
                      ->orWhere('referencia', 'LIKE', '%' . $request->referencia . '%');
                });
            }

            $movimientos = $query->get();

            $data = [];
            foreach ($movimientos as $movimiento) {
                $abonos = $movimiento->poliza->abonos ?? collect();
                $totalAbonado = $abonos->sum('monto_abonado');
                $saldoPendiente = abs($movimiento->monto) - $totalAbonado;
                $tienePdf = ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) ? 'Sí' : 'No';

                if ($vista === 'diferidas') {
                    $data[] = [
                        'vencimiento' => $this->formatFechaExcel($movimiento->poliza->fecha_vencimiento),
                        'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                        'nota' => $movimiento->poliza->nota ?? '—',
                        'monto' => $this->formatMonto($movimiento->monto),
                        'abonado' => $this->formatMonto($totalAbonado),
                        'saldo_pendiente' => $this->formatMonto($saldoPendiente),
                        'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : 'Sistema',
                        'estatus' => $this->getEstatusTexto($movimiento->poliza->estatus),
                    ];
                } else {
                    $data[] = [
                        'referencia' => $movimiento->poliza->folio ?? '—',
                        'fecha_poliza' => $this->formatFechaExcel($movimiento->poliza->fecha_poliza),
                        'tipo' => $movimiento->poliza->tipo_poliza ?? '—',
                        'estatus' => $this->getEstatusTexto($movimiento->poliza->estatus),
                        'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                        'cuenta' => $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                        'cuenta_fondeadora' => $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : 'N/A',
                        'nota' => $movimiento->poliza->nota ?? '—',
                        'monto' => $this->formatMonto($movimiento->monto),
                        'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : 'Sistema',
                        'pdf_fiscal' => $tienePdf
                    ];
                }
            }

            $empresa = \App\Models\Empresa::find($empresaId);
            $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Sin empresa';

            $pdf = Pdf::loadView('exports.movimientos_pdf', [
                'data' => $data,
                'vista' => $vista,
                'empresa' => $nombreEmpresa,
                'fecha_exportacion' => now()->format('d/m/Y H:i:s'),
                'filtros' => $request->all(),
                'es_diferidas' => $vista === 'diferidas'
            ]);

            $pdf->setPaper('A4', 'landscape');

            return $pdf->download('movimientos_' . date('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error al exportar PDF:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    // ============================================
    // 🔧 FUNCIONES AUXILIARES
    // ============================================
    
    /**
     * Obtener el texto del estatus
     */
    private function getEstatusTexto($estatus)
    {
        $map = [
            'CAPTURADO' => 'Capturado',
            'REVISADO' => 'Revisado',
            'AUTORIZADO' => 'Autorizado',
            'ABONADO' => 'Abonado',
            'LIQUIDADO' => 'Liquidado',
            'PENDIENTE' => 'Pendiente',
        ];
        return $map[$estatus] ?? $estatus;
    }

    private function actualizarEstatusPoliza($poliza)
    {
        $totalMovimientos = $poliza->movimientos()->sum('monto');
        $totalAbonado = $poliza->abonos()->sum('monto_abonado');

        // Si no tiene movimientos o está en estado CAPTURADO/REVISADO/AUTORIZADO
        if (in_array($poliza->estatus, ['CAPTURADO', 'REVISADO', 'AUTORIZADO'])) {
            // Si tiene abonos, cambiar a ABONADO
            if ($totalAbonado > 0) {
                $poliza->estatus = 'ABONADO';
                $poliza->fecha_abono = now();
            }
            // Si está totalmente pagado
            if ($totalAbonado >= abs($totalMovimientos) && $totalMovimientos != 0) {
                $poliza->estatus = 'LIQUIDADO';
                $poliza->fecha_liquidacion = now();
            }
        } elseif ($poliza->estatus === 'ABONADO') {
            // Si está totalmente pagado
            if ($totalAbonado >= abs($totalMovimientos) && $totalMovimientos != 0) {
                $poliza->estatus = 'LIQUIDADO';
                $poliza->fecha_liquidacion = now();
            }
        }

        $poliza->save();
        return $poliza;
    }

    private function actualizarSaldoCuenta($idCuenta)
    {
        if (!$idCuenta) return 0;
        
        $totalMovimientos = MovimientoPoliza::where(function($query) use ($idCuenta) {
            $query->where('id_cuenta', $idCuenta)
                  ->orWhere('id_caja_fondo', $idCuenta);
        })->sum('monto');
        
        $cuenta = Cuenta::find($idCuenta);
        if ($cuenta) {
            $cuenta->saldo_inicial = $totalMovimientos;
            $cuenta->save();
        }
        
        return $totalMovimientos;
    }

    public function getSaldoPendiente($movimiento)
    {
        $totalAbonado = AbonoPoliza::where('id_poliza', $movimiento->id_poliza)->sum('monto_abonado');
        return abs($movimiento->monto) - $totalAbonado;
    }

    private function formatFechaExcel($fecha)
    {
        if (!$fecha) return '—';
        if ($fecha instanceof \DateTime) {
            return $fecha->format('d/m/Y');
        }
        return date('d/m/Y', strtotime($fecha));
    }

    private function formatMonto($monto)
    {
        if ($monto === null || $monto === '') return '$0.00';
        $signo = $monto < 0 ? '-' : '';
        return $signo . '$' . number_format(abs($monto), 2);
    }

    // ============================================
    // 🧮 CALCULAR DESGLOSE EXACTO DE IVA
    // ============================================
    private function calcularDesgloseExacto($totalFactura, $porcentajeIva)
    {
        if ($porcentajeIva == 0) {
            return [
                'monto_base' => $totalFactura,
                'monto_iva' => 0,
                'total' => $totalFactura
            ];
        }

        $totalCentavos = round($totalFactura * 100);
        $factor = 100 + $porcentajeIva;
        
        $baseCentavos = round($totalCentavos * 100 / $factor);
        $ivaCentavos = $totalCentavos - $baseCentavos;
        
        $montoBase = $baseCentavos / 100;
        $montoIva = $ivaCentavos / 100;
        
        $suma = $montoBase + $montoIva;
        if (abs($suma - $totalFactura) > 0.001) {
            $diferencia = $totalFactura - $suma;
            $montoIva = round(($montoIva + $diferencia) * 100) / 100;
        }
        
        return [
            'monto_base' => round($montoBase, 2),
            'monto_iva' => round($montoIva, 2),
            'total' => round($montoBase + $montoIva, 2)
        ];
    }
}