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
    // 📄 INDEX - LISTADO DE MOVIMIENTOS (CON TRASPASOS)
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
        $soloFiscales = $request->boolean('solo_fiscales', false);

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

        if ($soloFiscales) {
            $query->whereHas('poliza', function($q) {
                $q->where('categoria', 'FISCAL');
            });
        }

        // ✅ FILTROS POR VISTA - INCLUYENDO TRASPASOS
        if ($vista === 'diferidas') {
            $query->whereHas('poliza', function($q) {
                $q->where('es_por_pagar', true);
            });
        } elseif ($vista === 'pendientes') {
            $query->whereHas('poliza', function($q) {
                $q->whereIn('estatus', ['PENDIENTE', 'CAPTURADO', 'REVISADO']);
            });
        } elseif ($vista === 'autorizadas') {
            $query->whereHas('poliza', function($q) {
                $q->where('estatus', 'AUTORIZADO');
            });
        } elseif ($vista === 'traspasos') {
            // ✅ NUEVA VISTA: SOLO TRASPASOS
            $query->whereHas('poliza', function($q) {
                $q->where('tipo_poliza', 'TRASPASO');
            });
        } else {
            // Vista normal: mostrar INGRESO, EGRESO y TRASPASO
            $query->whereHas('poliza', function($q) {
                $q->where('es_por_pagar', false);
            });
        }

        // FILTROS
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

        if ($request->filled('cuenta_fondeadora')) {
            $query->whereHas('cuentaFondeadora', function($q) use ($request) {
                $q->where('en_uso', true)
                ->where(function($sub) use ($request) {
                    $sub->where('nombre_cuenta', 'LIKE', '%' . $request->cuenta_fondeadora . '%')
                        ->orWhere('codigo_cuenta', 'LIKE', '%' . $request->cuenta_fondeadora . '%');
                });
            });
        }

        if ($request->filled('nota')) {
            $query->whereHas('poliza', function($q) use ($request) {
                $q->where('nota', 'LIKE', '%' . $request->nota . '%');
            });
        }

        if ($request->filled('usuario')) {
            $query->whereHas('poliza.usuarioCreador', function($q) use ($request) {
                $q->where('nombre_usuario', 'LIKE', '%' . $request->usuario . '%')
                ->orWhere('nombre_completo', 'LIKE', '%' . $request->usuario . '%');
            });
        }

        // ORDENAMIENTO
        $sortBy = $request->get('sort_by', 'fecha_poliza');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortMap = [
            'fecha_poliza' => 'polizas.fecha_poliza',
            'fecha_vencimiento' => 'polizas.fecha_vencimiento',
            'referencia' => 'polizas.folio',
            'estatus' => 'polizas.estatus',
            'monto' => 'movimientos_poliza.monto',
            'monto_traspaso' => 'movimientos_poliza.monto_traspaso',
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

            // ✅ DETECTAR SI ES TRASPASO
            $esTraspaso = $movimiento->poliza->tipo_poliza === 'TRASPASO';
            $montoMostrar = $esTraspaso ? $movimiento->monto_traspaso : $movimiento->monto;

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
                'monto' => $montoMostrar, // ✅ Mostrar monto_traspaso si es traspaso
                'monto_abs' => abs($montoMostrar),
                'monto_base' => $movimiento->monto_base,
                'monto_iva' => $movimiento->monto_iva,
                'monto_traspaso' => $movimiento->monto_traspaso,
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
                'es_traspaso' => $esTraspaso, // ✅ Flag para identificar traspasos
                'cuenta_destino' => $movimiento->cuentaDestino ? $movimiento->cuentaDestino->nombre_cuenta : null, // ✅ Cuenta destino del traspaso
            ];
        });

        $saldoTotal = null;
        if ($vista === 'diferidas') {
            $saldoTotal = $query->get()->sum(function($movimiento) {
                $totalAbonado = AbonoPoliza::where('id_poliza', $movimiento->id_poliza)->sum('monto_abonado');
                $monto = $movimiento->poliza->tipo_poliza === 'TRASPASO' ? $movimiento->monto_traspaso : $movimiento->monto;
                return abs($monto) - $totalAbonado;
            });
        } else {
            $saldoTotal = (float) $query->sum('monto');
        }

        $filtros = $request->only([
            'fecha_desde', 'fecha_hasta', 'referencia', 
            'estatus', 'tipo_poliza', 'persona', 'cuenta', 'cuenta_fondeadora', 'nota', 'usuario',
            'sort_by', 'sort_order', 'vista', 'mostrar_todos', 'solo_fiscales'
        ]);

        $contadores = [
            'capturados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'PENDIENTE')->count(),
            'revisados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'REVISADO')->count(),
            'autorizados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'AUTORIZADO')->count(),
            'abonados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'ABONADO')->count(),
            'liquidados' => Poliza::where('id_empresa', $empresaId)->where('estatus', 'LIQUIDADO')->count(),
            'traspasos' => Poliza::where('id_empresa', $empresaId)->where('tipo_poliza', 'TRASPASO')->count(), // ✅ Contador de traspasos
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

        // ✅ INCLUIR saldo_inicial EN LAS CUENTAS FONDEADORAS
        $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => (float) ($cuenta->saldo_inicial ?? 0)
                ];
            });

        // ✅ INCLUIR saldo_inicial EN LAS CUENTAS NORMALES TAMBIÉN
        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
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

   
    public function store(Request $request)
    {
        if (!Gate::allows('crear-movimientos')) {
            return redirect()->route('movimientos.index')
                ->with('error', 'No tienes permiso para crear movimientos');
        }

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
            \Log::error('Validación store fallida:', $validator->errors()->toArray());
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

            $rutaPdf = null;
            $nombrePdf = null;
            $nombreXml = null;

            // ✅ PROCESAR PDF
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                if ($pdfFile->isValid()) {
                    $nombrePdf = $pdfFile->getClientOriginalName();
                    $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                    \Log::info('PDF guardado:', ['ruta' => $rutaPdf]);
                }
            }

            if ($request->hasFile('xml_file')) {
                $xmlFile = $request->file('xml_file');
                \Log::info('Procesando XML:', [
                    'name' => $xmlFile->getClientOriginalName(),
                    'size' => $xmlFile->getSize(),
                    'mime' => $xmlFile->getMimeType(),
                    'is_valid' => $xmlFile->isValid()
                ]);
                
                if ($xmlFile->isValid()) {
                    $nombreXml = $xmlFile->getClientOriginalName();
                    \Log::info('XML procesado:', ['nombre' => $nombreXml]);
                } else {
                    \Log::error('XML no válido');
                }
            }

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
                'fecha_autorizacion' => null,
                'fecha_factura' => $request->fecha_factura,
                'numero_factura' => $request->numero_factura,
                'id_marcador' => $request->id_marcador,
                'fecha_creacion' => now(),
                'ruta_pdf' => $rutaPdf,
                'nombre_pdf' => $nombrePdf,
                'nombre_xml' => $nombreXml,
                'uuid_factura' => $request->uuid_factura,
                'serie_factura' => $request->serie_factura,
                'folio_factura' => $request->folio_factura,
            ]);

            $poliza->refresh();

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

            MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
                'id_tipo_iva' => $idTipoIva,
                'monto' => $monto,
                'monto_base' => $montoBase,
                'monto_iva' => $montoIva,
                'monto_traspaso' => null,
            ]);

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
                if ($nombreXml) $mensaje .= 'XML ✓';
            }

            \Log::info('Póliza creada exitosamente:', [
                'id' => $poliza->id,
                'folio' => $poliza->folio,
                'ruta_pdf' => $rutaPdf,
                'nombre_pdf' => $nombrePdf,
                'nombre_xml' => $nombreXml
            ]);

            return redirect()->route('movimientos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en store:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Error al crear la póliza: ' . $e->getMessage())
                ->withInput();
        }
    }


    // ============================================
    // 🔄 STORE TRASPASO - CON CUENTA ORIGEN EN id_caja_fondo Y DESTINO EN id_cuenta
    // ============================================
    public function storeTraspaso(Request $request)
    {
        \Log::info('=== INICIO storeTraspaso ===');
        \Log::info('Datos recibidos:', $request->all());

        if (!Gate::allows('crear-movimientos')) {
            \Log::warning('Permiso denegado para crear traspasos');
            return back()->with('error', 'No tienes permiso para crear traspasos');
        }

        // ✅ VALIDACIÓN
        $rules = [
            'tipo_poliza' => 'required|in:TRASPASO',
            'id_cuenta_origen' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_destino',
            'id_cuenta_destino' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_origen',
            'monto' => 'required|numeric|min:0.01',
            'es_fiscal' => 'nullable|in:true,false,1,0,on,off',
            'id_tipo_iva' => 'nullable|exists:tipos_iva,id',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'referencia' => 'nullable|string|max:100',
            'nota' => 'nullable|string',
            'fecha_factura' => 'nullable|date',
            'numero_factura' => 'nullable|string|max:50',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'xml_file' => 'nullable|file|max:5120',
            'uuid_factura' => 'nullable|string|max:50',
            'serie_factura' => 'nullable|string|max:20',
            'folio_factura' => 'nullable|string|max:20',
        ];

        // Si es fiscal, el PDF es obligatorio
        $esFiscal = filter_var($request->es_fiscal, FILTER_VALIDATE_BOOLEAN);
        if ($esFiscal) {
            $rules['pdf_file'] = 'required|file|mimes:pdf|max:10240';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Validación fallida:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        \Log::info('Validación pasó correctamente');

        try {
            DB::beginTransaction();
            \Log::info('Transacción iniciada');

            $empresaId = session('empresa_movimientos');
            \Log::info('Empresa ID desde sesión:', ['empresa_id' => $empresaId]);
            
            if (!$empresaId) {
                $empresa = auth()->user()->empresas()->first();
                $empresaId = $empresa ? $empresa->id : null;
                \Log::info('Empresa obtenida desde usuario:', ['empresa_id' => $empresaId]);
            }

            if (!$empresaId) {
                throw new \Exception('No se pudo determinar la empresa');
            }

            // ✅ VERIFICAR SALDO DE LA CUENTA ORIGEN
            $cuentaOrigen = Cuenta::find($request->id_cuenta_origen);
            $saldoOrigen = $cuentaOrigen ? (float) ($cuentaOrigen->saldo_inicial ?? 0) : 0;
            $monto = round($request->monto, 2);

            \Log::info('Cuenta origen:', [
                'id_cuenta' => $request->id_cuenta_origen,
                'saldo' => $saldoOrigen,
                'monto' => $monto
            ]);

            if ($saldoOrigen < $monto) {
                \Log::warning('Saldo insuficiente:', [
                    'saldo' => $saldoOrigen,
                    'monto' => $monto
                ]);
                return back()->with('error', "Saldo insuficiente en la cuenta origen. Disponible: $" . number_format($saldoOrigen, 2))->withInput();
            }

            $categoria = $esFiscal ? 'FISCAL' : 'NO_FISCAL';

            \Log::info('Configuración fiscal:', [
                'es_fiscal_raw' => $request->es_fiscal,
                'es_fiscal_parsed' => $esFiscal,
                'categoria' => $categoria
            ]);

            // ✅ GUARDAR ARCHIVOS - SOLO NOMBRE PARA XML
            $rutaPdf = null;
            $nombrePdf = null;
            $rutaXml = null;
            $nombreXml = null;

            \Log::info('Archivos recibidos:', [
                'has_pdf' => $request->hasFile('pdf_file'),
                'has_xml' => $request->hasFile('xml_file')
            ]);

            if ($esFiscal) {
                \Log::info('Procesando archivos fiscales...');
                
                // 📄 PROCESAR PDF (OBLIGATORIO)
                if ($request->hasFile('pdf_file')) {
                    $pdfFile = $request->file('pdf_file');
                    \Log::info('PDF File:', [
                        'name' => $pdfFile->getClientOriginalName(),
                        'size' => $pdfFile->getSize(),
                        'is_valid' => $pdfFile->isValid()
                    ]);
                    
                    if ($pdfFile->isValid()) {
                        $nombrePdf = $pdfFile->getClientOriginalName();
                        $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                        \Log::info('PDF guardado:', ['ruta' => $rutaPdf]);
                    }
                }

                // 📄 PROCESAR XML - SOLO GUARDA EL NOMBRE
                if ($request->hasFile('xml_file')) {
                    $xmlFile = $request->file('xml_file');
                    \Log::info('XML File:', [
                        'name' => $xmlFile->getClientOriginalName(),
                        'size' => $xmlFile->getSize(),
                        'is_valid' => $xmlFile->isValid()
                    ]);
                    
                    if ($xmlFile->isValid()) {
                        $nombreXml = $xmlFile->getClientOriginalName();
                        \Log::info('XML - Solo nombre guardado:', ['nombre' => $nombreXml]);
                    }
                }
            }

            \Log::info('Creando póliza con datos:', [
                'empresa_id' => $empresaId,
                'categoria' => $categoria,
                'referencia' => $request->referencia ?? 'TRASPASO-' . date('YmdHis'),
                'id_marcador' => $request->id_marcador
            ]);

            // ✅ CREAR PÓLIZA - CON ESTATUS 'CAPTURADO'
            $poliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => 'TRASPASO',
                'fecha_poliza' => now(),
                'fecha_vencimiento' => null,
                'categoria' => $categoria,
                'estatus' => 'CAPTURADO',  // ✅ CAMBIADO: Ahora es CAPTURADO
                'es_por_pagar' => false,
                'referencia' => $request->referencia ?? 'TRASPASO-' . date('YmdHis'),
                'nota' => $request->nota,
                'id_persona' => null,  // ✅ TRASPASO: SIN PERSONA
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'fecha_creacion' => now(),
                'fecha_autorizacion' => null,
                'fecha_factura' => $request->fecha_factura,
                'numero_factura' => $request->numero_factura,
                'id_marcador' => $request->id_marcador,
                'ruta_pdf' => $rutaPdf,
                'nombre_pdf' => $nombrePdf,
                'nombre_xml' => $nombreXml,
                'uuid_factura' => $request->uuid_factura,
                'serie_factura' => $request->serie_factura,
                'folio_factura' => $request->folio_factura,
            ]);

            \Log::info('Póliza creada:', [
                'id' => $poliza->id,
                'folio' => $poliza->folio,
                'estatus' => $poliza->estatus,
                'nombre_xml' => $nombreXml
            ]);

            $poliza->refresh();

            // ✅ CALCULAR IVA SI CORRESPONDE
            $montoBase = $monto;
            $montoIva = 0;
            $idTipoIva = null;

            if ($request->filled('id_tipo_iva')) {
                $idTipoIva = (int) $request->id_tipo_iva;
                $tipoIva = TipoIva::find($idTipoIva);
                \Log::info('Tipo IVA encontrado:', [
                    'id' => $idTipoIva,
                    'tipo_iva' => $tipoIva ? $tipoIva->toArray() : null
                ]);
                
                if ($tipoIva && $tipoIva->porcentaje > 0) {
                    $porcentaje = $tipoIva->porcentaje;
                    $factor = 1 + ($porcentaje / 100);
                    $montoBase = round($monto / $factor, 2);
                    $montoIva = round($monto - $montoBase, 2);
                    
                    \Log::info('Cálculo IVA:', [
                        'monto' => $monto,
                        'porcentaje' => $porcentaje,
                        'monto_base' => $montoBase,
                        'monto_iva' => $montoIva
                    ]);
                }
            }

            // ✅ CREAR MOVIMIENTO DE TRASPASO
            // id_cuenta = Cuenta DESTINO
            // id_caja_fondo = Cuenta ORIGEN (fondeadora)
            $movimiento = MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta_destino,        // ✅ Cuenta DESTINO
                'id_caja_fondo' => $request->id_cuenta_origen,    // ✅ Cuenta ORIGEN (fondeadora)
                'id_tipo_iva' => $idTipoIva,
                'monto_traspaso' => $monto,
                'monto' => 0,
                'monto_base' => 0,
                'monto_iva' => 0,
            ]);

            \Log::info('Movimiento de traspaso creado:', [
                'id' => $movimiento->id,
                'id_cuenta' => $movimiento->id_cuenta,           // Destino
                'id_caja_fondo' => $movimiento->id_caja_fondo,   // Origen
                'monto_traspaso' => $movimiento->monto_traspaso
            ]);

            // ✅ ACTUALIZAR SALDOS DE AMBAS CUENTAS
            $this->actualizarSaldoCuenta($request->id_cuenta_origen);
            $this->actualizarSaldoCuenta($request->id_cuenta_destino);

            DB::commit();
            \Log::info('Transacción completada exitosamente');

            $mensaje = "Traspaso creado exitosamente. Folio: {$poliza->folio} | Estado: CAPTURADO";
            if ($esFiscal) {
                $mensaje .= ' Documentos fiscales: ';
                if ($rutaPdf) $mensaje .= 'PDF ✓ ';
                if ($nombreXml) $mensaje .= 'XML (nombre guardado) ✓ ';
            }

            \Log::info('Mensaje de éxito:', ['mensaje' => $mensaje]);

            return redirect()->route('movimientos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en storeTraspaso:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al crear el traspaso: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $id)
{
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
    $totalAbonado = $abonos->sum('monto_abonado');
    $saldoPendiente = abs($movimiento->monto) - $totalAbonado;

    // ✅ DETECTAR TIPO DE PÓLIZA
    $tipoPoliza = $movimiento->poliza->tipo_poliza;
    $esTraspaso = $tipoPoliza === 'TRASPASO';
    $esIngresoEgreso = in_array($tipoPoliza, ['INGRESO', 'EGRESO']);
    $esFiscal = $movimiento->poliza->categoria === 'FISCAL';
    $esPorPagar = $movimiento->poliza->es_por_pagar ?? false;

    // ✅ PDF URL
    $pdfUrl = null;
    if ($esFiscal && !empty($movimiento->poliza->ruta_pdf)) {
        $pdfUrl = route('movimientos.documento.fiscal', [
            'id' => $movimiento->id_poliza,
            'tipo' => 'pdf'
        ]);
    }

    // ✅ XML URL
    $xmlUrl = null;
    if ($esFiscal && !empty($movimiento->poliza->ruta_xml)) {
        $xmlUrl = route('movimientos.documento.fiscal', [
            'id' => $movimiento->id_poliza,
            'tipo' => 'xml'
        ]);
    }

    // ✅ DATOS DE DOBLE IVA (si existen)
    $montoIvaCero = $movimiento->monto_iva_cero ?? 0;
    $montoIvaDieciseis = $movimiento->monto_iva_dieciseis ?? 0;
    $ivaDieciseis = $movimiento->iva_dieciseis ?? 0;
    $tieneDobleIva = ($montoIvaCero != 0 || $montoIvaDieciseis != 0 || $ivaDieciseis != 0);

    // ✅ DATOS DE TRASPASO
    $cuentaOrigen = null;
    $cuentaDestino = null;
    $montoTraspaso = null;
    
    if ($esTraspaso) {
        // Buscar movimientos origen y destino del traspaso
        $movimientosTraspaso = MovimientoPoliza::where('id_poliza', $movimiento->id_poliza)->get();
        
        // Origen: monto negativo o con monto_traspaso
        $movOrigen = $movimientosTraspaso->firstWhere(function($m) {
            return $m->monto < 0 || $m->monto_traspaso > 0;
        });
        
        // Destino: monto positivo
        $movDestino = $movimientosTraspaso->firstWhere(function($m) {
            return $m->monto > 0;
        });
        
        if ($movOrigen && $movOrigen->cuenta) {
            $cuentaOrigen = $movOrigen->cuenta->nombre_cuenta;
        }
        if ($movDestino && $movDestino->cuenta) {
            $cuentaDestino = $movDestino->cuenta->nombre_cuenta;
        }
        $montoTraspaso = $movimiento->monto_traspaso ?? abs($movimiento->monto);
    }

    // ✅ DATOS DEL MOVIMIENTO
    $movimientoData = [
        // 🔹 Datos básicos
        'id' => $movimiento->id,
        'id_poliza' => $movimiento->id_poliza,
        'folio' => $movimiento->poliza->folio,
        'tipo_poliza' => $tipoPoliza,
        'fecha_poliza' => $movimiento->poliza->fecha_poliza,
        'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento,
        'categoria' => $movimiento->poliza->categoria,
        'estatus' => $movimiento->poliza->estatus,
        'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
        'es_por_pagar' => $esPorPagar,
        'referencia' => $movimiento->poliza->referencia,
        'nota' => $movimiento->poliza->nota,

        // 🔹 Banderas de tipo
        'es_ingreso_egreso' => $esIngresoEgreso,
        'es_traspaso' => $esTraspaso,
        'es_fiscal' => $esFiscal,
        'es_por_pagar' => $esPorPagar,
        'tiene_doble_iva' => $tieneDobleIva,

        // 🔹 Persona y cuentas
        'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : null,
        'persona_id' => $movimiento->poliza->id_persona,
        'cuenta' => $movimiento->cuenta ? $movimiento->cuenta->nombre_cuenta : null,
        'cuenta_id' => $movimiento->id_cuenta,
        'cuenta_fondeadora' => $movimiento->cuentaFondeadora ? $movimiento->cuentaFondeadora->nombre_cuenta : null,
        'cuenta_fondeadora_id' => $movimiento->id_caja_fondo,

        // 🔹 Datos de traspaso
        'cuenta_origen' => $cuentaOrigen,
        'cuenta_destino' => $cuentaDestino,
        'monto_traspaso' => $montoTraspaso,

        // 🔹 Montos
        'monto' => $movimiento->monto,
        'monto_abs' => abs($movimiento->monto),
        'monto_base' => $movimiento->monto_base,
        'monto_iva' => $movimiento->monto_iva,

        // 🔹 Doble IVA
        'monto_iva_cero' => $montoIvaCero,
        'monto_iva_dieciseis' => $montoIvaDieciseis,
        'iva_dieciseis' => $ivaDieciseis,

        // 🔹 Abonos
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
        'total_abonado' => $totalAbonado,
        'saldo_pendiente' => $saldoPendiente,

        // 🔹 Marcador
        'marcador' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : null,

        // 🔹 Usuarios
        'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : null,
        'usuario_nombre' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_completo : null,
        'usuario_revisor' => $movimiento->poliza->usuarioRevisor ? $movimiento->poliza->usuarioRevisor->nombre_usuario : null,
        'usuario_autorizador' => $movimiento->poliza->usuarioAutorizador ? $movimiento->poliza->usuarioAutorizador->nombre_usuario : null,

        // 🔹 Fechas
        'fecha_revision' => $movimiento->poliza->fecha_revision,
        'fecha_autorizacion' => $movimiento->poliza->fecha_autorizacion,
        'fecha_factura' => $movimiento->poliza->fecha_factura,
        'numero_factura' => $movimiento->poliza->numero_factura,
        'fecha_creacion' => $movimiento->created_at,
        'fecha_actualizacion' => $movimiento->updated_at,

        // 🔹 Documentos fiscales
        'pdf_url' => $pdfUrl,
        'xml_url' => $xmlUrl,
        'tiene_pdf_fiscal' => !empty($movimiento->poliza->ruta_pdf),
        'tiene_xml_fiscal' => !empty($movimiento->poliza->ruta_xml),
        'uuid_factura' => $movimiento->poliza->uuid_factura,
        'serie_factura' => $movimiento->poliza->serie_factura,
        'folio_factura' => $movimiento->poliza->folio_factura,

        // 🔹 Comentarios
        'comentario_revision' => $movimiento->poliza->comentario_revision,
        'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
        'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
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
                    'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
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
                    'saldo' => (float) ($cuenta->saldo_inicial ?? 0)
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
            return back()->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    // ============================================
// 📄 DESTROY - ELIMINAR MOVIMIENTO (CON TRASPASOS)
// ============================================
public function destroy(string $id)
{
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
        
        // ✅ VERIFICAR SI ES TRASPASO
        $esTraspaso = $poliza->tipo_poliza === 'TRASPASO';
        
        // ✅ OBTENER TODOS LOS MOVIMIENTOS DE LA PÓLIZA (PARA TRASPASOS)
        $movimientosPoliza = MovimientoPoliza::where('id_poliza', $poliza->id)->get();
        
        // ✅ VERIFICAR ESTATUS - NO PERMITIR ELIMINAR PÓLIZAS AUTORIZADAS O LIQUIDADAS
        if (in_array($poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden eliminar movimientos de una póliza en estado ' . $this->getEstatusTexto($poliza->estatus)
            ], 422);
        }

        // ✅ RECOLECTAR IDS DE CUENTAS A ACTUALIZAR
        $cuentasAActualizar = [];
        
        foreach ($movimientosPoliza as $mov) {
            if ($mov->id_cuenta) {
                $cuentasAActualizar[] = $mov->id_cuenta;
            }
            if ($mov->id_caja_fondo) {
                $cuentasAActualizar[] = $mov->id_caja_fondo;
            }
        }
        
        // ✅ ELIMINAR ABONOS DE LA PÓLIZA
        AbonoPoliza::where('id_poliza', $poliza->id)->delete();

        // ✅ ELIMINAR DOCUMENTOS FISCALES ASOCIADOS
        if ($poliza->ruta_pdf && Storage::disk('public')->exists($poliza->ruta_pdf)) {
            Storage::disk('public')->delete($poliza->ruta_pdf);
        }
        if ($poliza->ruta_xml && Storage::disk('public')->exists($poliza->ruta_xml)) {
            Storage::disk('public')->delete($poliza->ruta_xml);
        }

        // ✅ ELIMINAR TODOS LOS MOVIMIENTOS DE LA PÓLIZA
        foreach ($movimientosPoliza as $mov) {
            $mov->delete();
        }

        // ✅ ELIMINAR LA PÓLIZA
        $poliza->delete();

        // ✅ ACTUALIZAR SALDOS DE TODAS LAS CUENTAS AFECTADAS
        foreach (array_unique($cuentasAActualizar) as $idCuenta) {
            if ($idCuenta) {
                $this->actualizarSaldoCuenta($idCuenta);
                \Log::info('Saldo actualizado para cuenta ID: ' . $idCuenta . ' después de eliminar póliza');
            }
        }

        DB::commit();

        $mensaje = 'Movimiento eliminado exitosamente';
        if ($esTraspaso) {
            $mensaje = 'Traspaso eliminado exitosamente. Los saldos de las cuentas han sido restaurados.';
        }

        \Log::info('Póliza eliminada:', [
            'id_poliza' => $poliza->id,
            'folio' => $poliza->folio,
            'tipo' => $poliza->tipo_poliza,
            'es_traspaso' => $esTraspaso,
            'cuentas_actualizadas' => array_unique($cuentasAActualizar)
        ]);

        return response()->json([
            'success' => true,
            'message' => $mensaje
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
            return back()->with('error', 'Error al registrar el abono: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // 📄 DESTROY ABONO
    // ============================================
    public function destroyAbono(string $id)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Error al liquidar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📄 CREATE NÓMINA
    // ============================================
    public function createNomina()
    {
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

        $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => (float) ($cuenta->saldo_inicial ?? 0)
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
                    'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
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
    // 📄 STORE NÓMINA
    // ============================================
    public function storeNomina(Request $request)
    {
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
                    'monto_iva' => 0,
                    'monto_traspaso' => null,
                ]);

                $this->actualizarSaldoCuenta($empleadoData['id_cuenta_fondeadora']);
                $this->actualizarSaldoCuenta($idCuenta);
            }

            DB::commit();

            return redirect()->route('movimientos.index')
                ->with('success', "Nómina generada exitosamente. {$totalEmpleados} pólizas creadas por un total de $" . number_format($totalNomina, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar las pólizas de nómina: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // 📄 CREATE FISCAL DOBLE IVA
    // ============================================
    public function createFiscalDobleIva()
    {
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
            ->where(function($query) {
                $query->where('en_uso', true)
                    ->orWhere('check_uso', 1);
            })
            ->where('fondeo_c', 1)
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo' => (float) ($cuenta->saldo_inicial ?? 0)
                ];
            });

        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where(function($query) {
                $query->where('en_uso', true)
                    ->orWhere('check_uso', 1);
            })
            ->orderBy('nombre_cuenta')
            ->get()
            ->map(function($cuenta) {
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
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

        $personas = Persona::where('activo', true)
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

        return Inertia::render('Movimientos/Poliza', [
            'empresa_id' => (int) $empresaId,
            'cuentas' => $cuentas,
            'cuentas_fondeadoras' => $cuentasFondeadoras,
            'marcadores' => $marcadores,
            'personas' => $personas,
        ]);
    }


    public function storeFiscalDobleIva(Request $request)
{
    \Log::info('=== INICIO storeFiscalDobleIva ===');
    \Log::info('Datos recibidos:', $request->all());

    if (!Gate::allows('crear-movimientos')) {
        \Log::warning('Permiso denegado para crear movimientos fiscales doble IVA');
        return back()->with('error', 'No tienes permiso para crear movimientos');
    }

    // ✅ Validación con los campos correctos
    $validator = Validator::make($request->all(), [
        'tipo_poliza' => 'required|in:INGRESO,EGRESO',
        'id_persona' => 'nullable|exists:personas,id_persona',
        'id_cuenta' => 'nullable|exists:cuentas,id_cuenta',
        'id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
        'es_por_pagar' => 'boolean',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        'id_marcador' => 'nullable|exists:marcadores,id',
        'monto_iva_cero' => 'required|numeric|min:0',
        'monto_iva_dieciseis' => 'required|numeric|min:0', // ✅ Campo corregido
        'total_factura' => 'required|numeric|min:0.01',
        'fecha_factura' => 'nullable|date',
        'numero_factura' => 'nullable|string|max:50',
        'nota' => 'nullable|string',
        'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        // ✅ XML es opcional - solo se pide
        'xml_file' => 'nullable|file|max:5120',
    ]);

    \Log::info('Validación ejecutada');

    if ($validator->fails()) {
        \Log::error('Validación fallida:', $validator->errors()->toArray());
        return back()->withErrors($validator)->withInput();
    }

    \Log::info('Validación pasó correctamente');

    try {
        \Log::info('Iniciando transacción DB');
        DB::beginTransaction();

        $empresaId = session('empresa_movimientos');
        \Log::info('Empresa ID desde sesión:', ['empresa_id' => $empresaId]);
        
        if (!$empresaId) {
            $empresa = auth()->user()->empresas()->first();
            $empresaId = $empresa ? $empresa->id : null;
            \Log::info('Empresa obtenida desde usuario:', ['empresa_id' => $empresaId]);
        }

        if (!$empresaId) {
            throw new \Exception('No se pudo determinar la empresa');
        }

        $esPorPagar = filter_var($request->es_por_pagar, FILTER_VALIDATE_BOOLEAN);
        $categoria = 'FISCAL';
        \Log::info('Configuración:', [
            'es_por_pagar' => $esPorPagar,
            'categoria' => $categoria
        ]);

        // ✅ PROCESAR PDF (SI VIENE)
        $rutaPdf = null;
        $nombrePdf = null;
        \Log::info('Procesando PDF:', ['has_file' => $request->hasFile('pdf_file')]);

        if ($request->hasFile('pdf_file')) {
            $pdfFile = $request->file('pdf_file');
            \Log::info('PDF recibido:', [
                'name' => $pdfFile->getClientOriginalName(),
                'size' => $pdfFile->getSize(),
                'is_valid' => $pdfFile->isValid()
            ]);
            if ($pdfFile->isValid()) {
                $nombrePdf = $pdfFile->getClientOriginalName();
                $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                \Log::info('PDF guardado:', ['ruta' => $rutaPdf]);
            } else {
                \Log::error('PDF no válido');
            }
        }

        // ✅ SOLO OBTENER EL NOMBRE DEL XML - NO SE GUARDA FÍSICAMENTE
        $nombreXml = null;
        \Log::info('Procesando XML (SOLO NOMBRE):', ['has_file' => $request->hasFile('xml_file')]);

        if ($request->hasFile('xml_file')) {
            $xmlFile = $request->file('xml_file');
            \Log::info('XML recibido:', [
                'name' => $xmlFile->getClientOriginalName(),
                'size' => $xmlFile->getSize(),
                'is_valid' => $xmlFile->isValid()
            ]);
            if ($xmlFile->isValid()) {
                $nombreXml = $xmlFile->getClientOriginalName();
                \Log::info('XML procesado - solo nombre guardado:', ['nombre' => $nombreXml]);
            } else {
                \Log::error('XML no válido');
            }
        }

        // ✅ OBTENER LOS MONTOS
        $montoIvaCero = round($request->monto_iva_cero, 2);
        $montoIvaDieciseis = round($request->monto_iva_dieciseis, 2);
        $totalFactura = round($request->total_factura, 2);

        // ✅ CALCULAR IVA DEL 16%
        $ivaDieciseis = round($montoIvaDieciseis * 0.16, 2);
        $montoTotal = $montoIvaCero + $montoIvaDieciseis + $ivaDieciseis;

        $signo = $request->tipo_poliza === 'EGRESO' ? -1 : 1;

        \Log::info('Cálculos detallados:', [
            'monto_iva_cero' => $montoIvaCero,
            'monto_iva_dieciseis' => $montoIvaDieciseis,
            'iva_dieciseis_calculado' => $ivaDieciseis,
            'monto_total_sin_signo' => $montoTotal,
            'signo' => $signo,
            'monto_total_con_signo' => round($montoTotal * $signo, 2)
        ]);

        // ✅ CREAR PÓLIZA
        $poliza = Poliza::create([
            'id_empresa' => $empresaId,
            'tipo_poliza' => $request->tipo_poliza,
            'fecha_poliza' => now(),
            'fecha_vencimiento' => $esPorPagar ? $request->fecha_vencimiento : null,
            'categoria' => $categoria,
            'estatus' => 'CAPTURADO',
            'es_por_pagar' => $esPorPagar,
            'referencia' => 'FISCAL-DOBLE-IVA-' . date('YmdHis'),
            'nota' => $request->nota,
            'id_persona' => $request->id_persona,
            'id_usuario_creador' => auth()->id(),
            'id_usuario_autorizador' => null,
            'fecha_autorizacion' => null,
            'fecha_factura' => $request->fecha_factura,
            'numero_factura' => $request->numero_factura,
            'id_marcador' => $request->id_marcador,
            'fecha_creacion' => now(),
            'ruta_pdf' => $rutaPdf,
            'nombre_pdf' => $nombrePdf,
            'nombre_xml' => $nombreXml,
        ]);

        \Log::info('Póliza creada:', ['id' => $poliza->id, 'folio' => $poliza->folio]);

        $poliza->refresh();

        // ✅ CREAR MOVIMIENTO CON LOS NUEVOS CAMPOS
        $movimiento = MovimientoPoliza::create([
            'id_poliza' => $poliza->id,
            'id_cuenta' => $request->id_cuenta,
            'id_caja_fondo' => $request->id_cuenta_fondeadora,
            'id_tipo_iva' => null,
            'monto' => round($montoTotal * $signo, 2),
            'monto_base' => round(($montoIvaCero + $montoIvaDieciseis) * $signo, 2),
            'monto_iva' => round($ivaDieciseis * $signo, 2),
            'monto_traspaso' => null,
            // ✅ NUEVOS CAMPOS PARA DOBLE IVA
            'monto_iva_cero' => round($montoIvaCero * $signo, 2),
            'monto_iva_dieciseis' => round($montoIvaDieciseis * $signo, 2),
            'iva_dieciseis' => round($ivaDieciseis * $signo, 2),
        ]);

        \Log::info('Movimiento creado con doble IVA:', [
            'id' => $movimiento->id,
            'monto_iva_cero' => $movimiento->monto_iva_cero,
            'monto_iva_dieciseis' => $movimiento->monto_iva_dieciseis,
            'iva_dieciseis' => $movimiento->iva_dieciseis,
            'monto_total' => $movimiento->monto
        ]);

        // ✅ ACTUALIZAR SALDOS
        if ($request->id_cuenta_fondeadora) {
            \Log::info('Actualizando saldo cuenta fondeadora:', ['id' => $request->id_cuenta_fondeadora]);
            $this->actualizarSaldoCuenta($request->id_cuenta_fondeadora);
        }
        
        if ($request->id_cuenta && $request->id_cuenta != $request->id_cuenta_fondeadora) {
            \Log::info('Actualizando saldo cuenta:', ['id' => $request->id_cuenta]);
            $this->actualizarSaldoCuenta($request->id_cuenta);
        }

        DB::commit();
        \Log::info('✅ Transacción completada exitosamente');

        // ✅ MENSAJE DE ÉXITO CON DETALLES DEL DOBLE IVA
        $mensaje = sprintf(
            "Póliza fiscal con doble IVA creada exitosamente. Folio: %s | " .
            "IVA 0%%: $%.2f | IVA 16%%: $%.2f | IVA calculado: $%.2f | Total: $%.2f",
            $poliza->folio,
            $montoIvaCero,
            $montoIvaDieciseis,
            $ivaDieciseis,
            $montoTotal
        );

        return redirect()->route('movimientos.index')
            ->with('success', $mensaje);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('❌ Error en storeFiscalDobleIva:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Error al crear la póliza: ' . $e->getMessage())->withInput();
    }
}

    // ============================================
    // ✅ REVISAR PÓLIZA (CAPTURADO → REVISADO)
    // ============================================
    public function revisarPoliza(Request $request, $id)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Error al revertir la revisión: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📄 OBTENER DOCUMENTO FISCAL (PDF o XML)
    // ============================================
    public function getDocumentoFiscal($id, $tipo)
    {
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
    // 🔍 BUSCAR CUENTAS (API)
    // ============================================
    public function buscarCuentas(Request $request)
    {
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
                    'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
                ];
            });

        return response()->json($cuentas);
    }

    // ============================================
    // 📝 OBTENER EMPLEADOS PARA NÓMINA (API)
    // ============================================
    public function getEmpleadosNomina(Request $request)
    {
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
    // 📊 EXPORTAR EXCEL
    // ============================================
    public function exportExcel(Request $request)
    {
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

            if ($request->boolean('solo_fiscales')) {
                $query->whereHas('poliza', function($q) {
                    $q->where('categoria', 'FISCAL');
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
            return redirect()->back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    // ============================================
    // 📄 EXPORTAR PDF
    // ============================================
    public function exportPdf(Request $request)
    {
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

            if ($request->boolean('solo_fiscales')) {
                $query->whereHas('poliza', function($q) {
                    $q->where('categoria', 'FISCAL');
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
            return redirect()->back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    // ============================================
    // 🔧 FUNCIONES AUXILIARES
    // ============================================
    
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

        if (in_array($poliza->estatus, ['CAPTURADO', 'REVISADO', 'AUTORIZADO'])) {
            if ($totalAbonado > 0) {
                $poliza->estatus = 'ABONADO';
                $poliza->fecha_abono = now();
            }
            if ($totalAbonado >= abs($totalMovimientos) && $totalMovimientos != 0) {
                $poliza->estatus = 'LIQUIDADO';
                $poliza->fecha_liquidacion = now();
            }
        } elseif ($poliza->estatus === 'ABONADO') {
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
    
    \Log::info('Actualizando saldo de cuenta:', ['id_cuenta' => $idCuenta]);
    
    // 🔥 Calcular saldo basado en movimientos
    // Para traspasos:
    // - Si la cuenta es ORIGEN (id_caja_fondo): se RESTA el monto_traspaso
    // - Si la cuenta es DESTINO (id_cuenta): se SUMA el monto_traspaso
    
    $totalMonto = MovimientoPoliza::where(function($query) use ($idCuenta) {
        $query->where('id_cuenta', $idCuenta)
              ->orWhere('id_caja_fondo', $idCuenta);
    })->sum('monto');
    
    // 🔥 Traspasos donde esta cuenta es ORIGEN (id_caja_fondo) - RESTAR
    $traspasosOrigen = MovimientoPoliza::where('id_caja_fondo', $idCuenta)
        ->whereNotNull('monto_traspaso')
        ->where('monto_traspaso', '>', 0)
        ->sum('monto_traspaso');
    
    // 🔥 Traspasos donde esta cuenta es DESTINO (id_cuenta) - SUMAR
    $traspasosDestino = MovimientoPoliza::where('id_cuenta', $idCuenta)
        ->whereNotNull('monto_traspaso')
        ->where('monto_traspaso', '>', 0)
        ->sum('monto_traspaso');
    
    // ✅ Calcular saldo total
    $saldoTotal = $totalMonto - $traspasosOrigen + $traspasosDestino;
    
    \Log::info('Cálculo de saldo:', [
        'id_cuenta' => $idCuenta,
        'total_monto' => $totalMonto,
        'traspasos_origen' => $traspasosOrigen,
        'traspasos_destino' => $traspasosDestino,
        'saldo_total' => $saldoTotal
    ]);
    
    $cuenta = Cuenta::find($idCuenta);
    if ($cuenta) {
        $cuenta->saldo_inicial = $saldoTotal;
        $cuenta->save();
        \Log::info('Cuenta actualizada:', [
            'id' => $cuenta->id_cuenta,
            'nuevo_saldo' => $saldoTotal
        ]);
    } else {
        \Log::warning('Cuenta no encontrada:', ['id_cuenta' => $idCuenta]);
    }
    
    return $saldoTotal;
}

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
// 🔍 BUSCAR PERSONAS (API)
// ============================================
public function buscarPersonas(Request $request)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json([], 403);
    }

    $search = $request->get('q', '');
    $empresaId = session('empresa_movimientos');

    $personas = Persona::where('activo', true)
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

    return response()->json($personas);
}

// ============================================
// 💰 OBTENER SALDO DE CUENTA (API)
// ============================================
public function saldoCuenta(Request $request)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json(['error' => 'No tienes permiso'], 403);
    }

    $idCuenta = $request->get('id');
    if (!$idCuenta) {
        return response()->json(['error' => 'ID de cuenta requerido'], 400);
    }

    $cuenta = Cuenta::find($idCuenta);
    if (!$cuenta) {
        return response()->json(['error' => 'Cuenta no encontrada'], 404);
    }

    // 🔥 Calcular saldo real de la cuenta
    $saldo = $this->actualizarSaldoCuenta($idCuenta);

    return response()->json([
        'id_cuenta' => $cuenta->id_cuenta,
        'nombre_cuenta' => $cuenta->nombre_cuenta,
        'saldo' => (float) $saldo,
        'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0)
    ]);
}


// ============================================
// 📄 OBTENER ÚLTIMA PÓLIZA DE UNA PERSONA (API)
// ============================================
public function obtenerUltimaPoliza(Request $request)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json(['error' => 'No tienes permiso'], 403);
    }

    $idPersona = $request->get('id');
    if (!$idPersona) {
        return response()->json(['error' => 'ID de persona requerido'], 400);
    }

    // ✅ Verificar que la persona existe
    $persona = Persona::find($idPersona);
    if (!$persona) {
        return response()->json(['error' => 'Persona no encontrada'], 404);
    }

    // ✅ Buscar la última póliza de esta persona
    $ultimaPoliza = Poliza::where('id_persona', $idPersona)
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$ultimaPoliza) {
        return response()->json([
            'existe' => false,
            'persona_nombre' => $persona->nombre_completo,
            'mensaje' => 'No hay pólizas registradas para esta persona'
        ]);
    }

    // ✅ Obtener el movimiento asociado a la última póliza
    $movimiento = MovimientoPoliza::where('id_poliza', $ultimaPoliza->id)->first();

    if (!$movimiento) {
        return response()->json([
            'existe' => false,
            'persona_nombre' => $persona->nombre_completo,
            'mensaje' => 'La póliza no tiene movimiento asociado'
        ]);
    }

    // ✅ Datos a auto-llenar
    return response()->json([
        'existe' => true,
        'persona_nombre' => $persona->nombre_completo,
        'id_cuenta' => $movimiento->id_cuenta,
        'es_por_pagar' => (bool) $ultimaPoliza->es_por_pagar,
        'fecha_vencimiento' => $ultimaPoliza->fecha_vencimiento ? $ultimaPoliza->fecha_vencimiento->format('Y-m-d') : null,
        'es_fiscal' => $ultimaPoliza->categoria === 'FISCAL',
        'id_marcador' => $ultimaPoliza->id_marcador,
        'id_tipo_iva' => $movimiento->id_tipo_iva,
        'concepto' => $ultimaPoliza->nota ?? 'Servicio general',
        'fecha_factura' => $ultimaPoliza->fecha_factura ? $ultimaPoliza->fecha_factura->format('Y-m-d') : null,
        'numero_factura' => $ultimaPoliza->numero_factura,
        'nota' => $ultimaPoliza->nota,
        'referencia' => $ultimaPoliza->referencia,
        'id_cuenta_fondeadora' => $movimiento->id_caja_fondo, // También podemos auto-llenar la fondeadora
    ]);
}

    // ============================================
    // 📄 EXPORTAR PÓLIZA A PDF
    // ============================================
    public function exportPdfPoliza($id)
    {
        if (!Gate::allows('ver-movimientos')) {
            abort(403, 'No tienes permiso para ver esta póliza');
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
            abort(404, 'Movimiento no encontrado');
        }

        $esTraspaso = $movimiento->poliza->tipo_poliza === 'TRASPASO';
        $abonos = $movimiento->poliza->abonos ?? collect();
        $totalAbonado = $abonos->sum('monto_abonado');
        
        if ($esTraspaso) {
            $saldoPendiente = ($movimiento->monto_traspaso ?? 0) - $totalAbonado;
            $montoMostrar = $movimiento->monto_traspaso ?? 0;
        } else {
            $saldoPendiente = abs($movimiento->monto) - $totalAbonado;
            $montoMostrar = $movimiento->monto;
        }

        $data = [
            'movimiento' => $movimiento,
            'esTraspaso' => $esTraspaso,
            'montoMostrar' => $montoMostrar,
            'totalAbonado' => $totalAbonado,
            'saldoPendiente' => $saldoPendiente,
            'empresa' => $movimiento->poliza->empresa,
            'fecha_exportacion' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('exports.poliza_pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('poliza_' . ($movimiento->poliza->folio ?? 'sin_folio') . '.pdf');
    }


    // ============================================
// 🔄 CERRAR PÓLIZA (CAMBIA ESTATUS A CERRADO)
// ============================================
public function cerrarPoliza(Request $request, $id)
{
    if (!Gate::allows('editar-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para cerrar pólizas'
        ], 403);
    }

    try {
        DB::beginTransaction();

        $poliza = Poliza::findOrFail($id);
        
        if (in_array($poliza->estatus, ['LIQUIDADO', 'AUTORIZADO'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cerrar una póliza en estado ' . $this->getEstatusTexto($poliza->estatus)
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'motivo' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Guardar estado anterior antes de cerrar
        $estadoAnterior = $poliza->estatus;
        
        $poliza->estatus = 'CERRADO';
        $poliza->fecha_cierre = now();
        $poliza->motivo_cierre = $request->motivo ?? 'Cierre manual de póliza';
        $poliza->id_usuario_cierre = auth()->id();
        $poliza->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Póliza cerrada exitosamente',
            'data' => [
                'estatus' => $poliza->estatus,
                'estatus_texto' => $this->getEstatusTexto($poliza->estatus),
                'fecha_cierre' => $poliza->fecha_cierre?->format('d/m/Y H:i')
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al cerrar la póliza: ' . $e->getMessage()
        ], 500);
    }
}

// ============================================
// 📄 REABRIR PÓLIZA (CERRADO → CAPTURADO)
// ============================================
public function reabrirPoliza(Request $request, $id)
{
    if (!Gate::allows('editar-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para reabrir pólizas'
        ], 403);
    }

    try {
        DB::beginTransaction();

        $poliza = Poliza::findOrFail($id);
        
        if ($poliza->estatus !== 'CERRADO') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden reabrir pólizas en estado CERRADO'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'motivo' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $poliza->estatus = 'CAPTURADO';
        $poliza->fecha_cierre = null;
        $poliza->motivo_cierre = null;
        $poliza->id_usuario_cierre = null;
        $poliza->motivo_rechazo = $request->motivo ?? 'Póliza reabierta';
        $poliza->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Póliza reabierta exitosamente. Regresa a estado CAPTURADO',
            'data' => [
                'estatus' => $poliza->estatus,
                'estatus_texto' => $this->getEstatusTexto($poliza->estatus)
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al reabrir la póliza: ' . $e->getMessage()
        ], 500);
    }
}

// ============================================
// 📄 OBTENER DETALLE DE PÓLIZA PARA SHOW (API)
// ============================================
public function getDetallePoliza($id)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para ver esta póliza'
        ], 403);
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
        return response()->json([
            'success' => false,
            'message' => 'Movimiento no encontrado'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $movimiento
    ]);
}

// ============================================
// 📊 OBTENER ESTADÍSTICAS DE LA PÓLIZA
// ============================================
public function getEstadisticasPoliza($id)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso'
        ], 403);
    }

    $movimiento = MovimientoPoliza::with(['poliza.abonos'])->find($id);

    if (!$movimiento) {
        return response()->json([
            'success' => false,
            'message' => 'Movimiento no encontrado'
        ], 404);
    }

    $abonos = $movimiento->poliza->abonos ?? collect();
    $totalAbonado = $abonos->sum('monto_abonado');
    $montoTotal = abs($movimiento->monto);
    $saldoPendiente = $montoTotal - $totalAbonado;
    $porcentajeAbonado = $montoTotal > 0 ? ($totalAbonado / $montoTotal) * 100 : 0;

    return response()->json([
        'success' => true,
        'data' => [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'saldo_pendiente' => $saldoPendiente,
            'porcentaje_abonado' => round($porcentajeAbonado, 2),
            'numero_abonos' => $abonos->count(),
            'ultimo_abono' => $abonos->last(),
            'estatus' => $movimiento->poliza->estatus
        ]
    ]);
}

// ============================================
// 📝 OBTENER HISTORIAL DE CAMBIOS DE LA PÓLIZA
// ============================================
public function getHistorialPoliza($id)
{
    if (!Gate::allows('ver-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso'
        ], 403);
    }

    $poliza = Poliza::with([
        'usuarioCreador',
        'usuarioRevisor',
        'usuarioAutorizador'
    ])->find($id);

    if (!$poliza) {
        return response()->json([
            'success' => false,
            'message' => 'Póliza no encontrada'
        ], 404);
    }

    $historial = [];

    // ✅ Evento de creación
    $historial[] = [
        'fecha' => $poliza->created_at?->format('d/m/Y H:i'),
        'evento' => 'CREACIÓN',
        'descripcion' => 'Póliza creada por ' . ($poliza->usuarioCreador?->nombre_completo ?? 'Sistema'),
        'usuario' => $poliza->usuarioCreador?->nombre_completo ?? 'Sistema',
        'estatus' => 'CAPTURADO'
    ];

    // ✅ Evento de revisión
    if ($poliza->fecha_revision && $poliza->usuarioRevisor) {
        $historial[] = [
            'fecha' => $poliza->fecha_revision?->format('d/m/Y H:i'),
            'evento' => 'REVISIÓN',
            'descripcion' => 'Póliza revisada por ' . ($poliza->usuarioRevisor?->nombre_completo ?? 'Sistema'),
            'usuario' => $poliza->usuarioRevisor?->nombre_completo ?? 'Sistema',
            'estatus' => 'REVISADO',
            'comentario' => $poliza->comentario_revision
        ];
    }

    // ✅ Evento de autorización
    if ($poliza->fecha_autorizacion && $poliza->usuarioAutorizador) {
        $historial[] = [
            'fecha' => $poliza->fecha_autorizacion?->format('d/m/Y H:i'),
            'evento' => 'AUTORIZACIÓN',
            'descripcion' => 'Póliza autorizada por ' . ($poliza->usuarioAutorizador?->nombre_completo ?? 'Sistema'),
            'usuario' => $poliza->usuarioAutorizador?->nombre_completo ?? 'Sistema',
            'estatus' => 'AUTORIZADO',
            'comentario' => $poliza->comentario_autorizacion
        ];
    }

    // ✅ Evento de cierre
    if ($poliza->fecha_cierre) {
        $historial[] = [
            'fecha' => $poliza->fecha_cierre?->format('d/m/Y H:i'),
            'evento' => 'CIERRE',
            'descripcion' => 'Póliza cerrada',
            'usuario' => $poliza->usuarioCierre?->nombre_completo ?? 'Sistema',
            'estatus' => 'CERRADO',
            'motivo' => $poliza->motivo_cierre
        ];
    }

    // ✅ Evento de rechazo
    if ($poliza->motivo_rechazo) {
        $historial[] = [
            'fecha' => $poliza->updated_at?->format('d/m/Y H:i'),
            'evento' => 'RECHAZO',
            'descripcion' => 'Póliza rechazada',
            'usuario' => auth()->user()?->nombre_completo ?? 'Sistema',
            'estatus' => 'RECHAZADO',
            'motivo' => $poliza->motivo_rechazo
        ];
    }

    // ✅ Ordenar por fecha (más reciente primero)
    usort($historial, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });

    return response()->json([
        'success' => true,
        'data' => $historial
    ]);
}
}