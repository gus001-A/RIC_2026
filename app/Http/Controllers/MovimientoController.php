<?php

namespace App\Http\Controllers;

use App\Models\MovimientoPoliza;
use App\Models\Poliza;
use App\Models\Cuenta;
use App\Models\TipoIva;
use App\Models\Persona;
use App\Models\Marcador;
use App\Models\AbonoPoliza;
use App\Models\PolizaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon; 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class MovimientoController extends Controller
{


public function index(Request $request)
{
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

    // 🔥 INTENTAR OBTENER EMPRESA DE: 1) Request, 2) Sesión
    $empresaId = $request->input('empresa_id');
    
    if (!$empresaId) {
        $empresaId = session('empresa_seleccionada');
    }
    
    // Validar que el usuario tenga acceso a la empresa
    if ($empresaId && !$empresas->contains('id', $empresaId)) {
        $empresaId = null;
    }
    
    // Si aún no hay, usar la primera empresa disponible
    if (!$empresaId && $empresas->count() > 0) {
        $empresaId = $empresas->first()->id;
    }
    
    // 🔥 GUARDAR EN SESIÓN para persistir entre requests
    if ($empresaId) {
        session(['empresa_seleccionada' => $empresaId]);
    }

    $vista = $request->get('vista', 'normal');
    $soloFiscales = $request->boolean('solo_fiscales', false);

    $query = MovimientoPoliza::with([
        'poliza.persona',
        'poliza.usuarioCreador',
        'poliza.usuarioRevisor',
        'poliza.usuarioAutorizador',
        'poliza.archivos', // 🔥 NUEVO: Cargar archivos adjuntos de la póliza
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
        $query->whereHas('poliza', function($q) {
            $q->where('tipo_poliza', 'TRASPASO');
        });
    } else {
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
        
        $esTraspaso = $movimiento->poliza->tipo_poliza === 'TRASPASO';
        $montoMostrar = $esTraspaso ? $movimiento->monto_traspaso : $movimiento->monto;
        $saldoPendiente = abs($montoMostrar) - $totalAbonado;

        $pdfUrl = null;
        if ($movimiento->poliza->categoria === 'FISCAL' && !empty($movimiento->poliza->ruta_pdf)) {
            $pdfUrl = route('movimientos.documento.fiscal', [
                'id' => $movimiento->id_poliza,
                'tipo' => 'pdf'
            ]);
        }

        $cuentaDestino = null;
        if ($esTraspaso) {
            $movDestino = MovimientoPoliza::where('id_poliza', $movimiento->id_poliza)
                ->where('id_cuenta', '!=', $movimiento->id_cuenta)
                ->first();
            if ($movDestino && $movDestino->cuenta) {
                $cuentaDestino = $movDestino->cuenta->nombre_cuenta;
            }
        }

        // 🔥 OBTENER EL RECURSO (ARCHIVO ADJUNTO) DE LA PÓLIZA
        $recurso = $movimiento->poliza->archivos->first(); // Solo el primer archivo
        $tieneRecurso = $recurso ? true : false;
        $recursoUrl = $recurso ? route('movimientos.archivos.ver', $recurso->id) : null;
        $recursoTipo = $recurso ? $recurso->tipo_archivo : null;

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
            'monto' => $montoMostrar,
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
            'es_traspaso' => $esTraspaso,
            'cuenta_destino' => $cuentaDestino,
            // 🔥 NUEVOS CAMPOS PARA EL RECURSO
            'tiene_recurso' => $tieneRecurso,
            'recurso_url' => $recursoUrl,
            'recurso_tipo' => $recursoTipo,
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
        'traspasos' => Poliza::where('id_empresa', $empresaId)->where('tipo_poliza', 'TRASPASO')->count(),
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

public function create()
{
    if (!Gate::allows('crear-movimientos')) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No tienes permiso para crear movimientos');
    }

    // 🔥 OBTENER EMPRESA DE SESIÓN (usando ambas claves)
    $empresaId = session('empresa_movimientos') ?? session('empresa_seleccionada');
    
    if (!$empresaId) {
        $empresa = auth()->user()->empresas()->first();
        $empresaId = $empresa ? $empresa->id : null;
    }

    if (!$empresaId) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No tienes una empresa seleccionada.');
    }

    // Guardar en sesión para consistencia
    session(['empresa_movimientos' => $empresaId]);
    session(['empresa_seleccionada' => $empresaId]);

    // 🔥 CUENTAS FONDEADORAS (solo fondeo_c = 1 y activas)
    $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where('fondeo_c', 1)
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo' => (float) ($cuenta->saldo_inicial ?? 0),
                'Naturaleza' => $cuenta->Naturaleza,
                'tipo' => 'fondeadora'
            ];
        });

    // 🔥 CUENTAS PARA EGRESOS (DÉBITO) - NO fondeadoras
    $cuentasEgreso = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where(function($query) {
            // Excluir fondeadoras
            $query->where('fondeo_c', '!=', 1)
                  ->orWhereNull('fondeo_c');
        })
        ->where(function($query) {
            // Pueden recibir débito
            $query->where('es_cuenta_resultados', true)
                  ->orWhereNull('Naturaleza')
                  ->orWhere('Naturaleza', 'DEUDORA');
        })
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'Naturaleza' => $cuenta->Naturaleza ?? 'SIN NATURALEZA',
                'es_cuenta_resultados' => (bool) $cuenta->es_cuenta_resultados,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0),
                'tipo_movimiento' => 'egreso'
            ];
        });

    // 🔥 CUENTAS PARA INGRESOS (CRÉDITO) - NO fondeadoras
    $cuentasIngreso = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where(function($query) {
            // Excluir fondeadoras
            $query->where('fondeo_c', '!=', 1)
                  ->orWhereNull('fondeo_c');
        })
        ->where(function($query) {
            // Pueden recibir crédito
            $query->where('es_cuenta_resultados', true)
                  ->orWhereNull('Naturaleza')
                  ->orWhere('Naturaleza', 'ACREEDORA');
        })
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'Naturaleza' => $cuenta->Naturaleza ?? 'SIN NATURALEZA',
                'es_cuenta_resultados' => (bool) $cuenta->es_cuenta_resultados,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0),
                'tipo_movimiento' => 'ingreso'
            ];
        });

    // 🔥 OBTENER PERSONAS - CORREGIDO CON LOS NOMBRES CORRECTOS DE COLUMNAS
    $personas = \App\Models\Persona::where('activo', true)
        ->orderBy('Nombre')  // ← CORREGIDO: 'Nombre' con mayúscula
        ->get(['id_persona', 'Nombre', 'Paterno', 'Materno'])  // ← CORREGIDO: nombres de columnas correctos
        ->map(function($persona) {
            // Construir nombre completo
            $nombreCompleto = $persona->Nombre;
            if ($persona->Paterno) {
                $nombreCompleto .= ' ' . $persona->Paterno;
            }
            if ($persona->Materno) {
                $nombreCompleto .= ' ' . $persona->Materno;
            }
            
            return [
                'id_persona' => $persona->id_persona,
                'nombre_completo' => $nombreCompleto,
                'nombre' => $persona->Nombre,
                'paterno' => $persona->Paterno,
                'materno' => $persona->Materno
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
        'cuentas_egreso' => $cuentasEgreso,
        'cuentas_ingreso' => $cuentasIngreso,
        'cuentas_fondeadoras' => $cuentasFondeadoras,
        'tipos_iva' => $tiposIva,
        'marcadores' => $marcadores,
        'personas' => $personas
    ]);
}
   // ============================================
// 📄 STORE - CREAR PÓLIZA CON VALIDACIÓN DE NATURALEZA
// ============================================
public function store(Request $request)
{
    if (!Gate::allows('crear-movimientos')) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No tienes permiso para crear movimientos');
    }

    $rules = [
        'tipo_poliza' => 'required|in:INGRESO,EGRESO',
        'fecha_poliza' => 'required|date',
        'id_persona' => 'nullable|exists:personas,id_persona',
        'id_cuenta' => 'required|exists:cuentas,id_cuenta',
        'es_por_pagar' => 'nullable|in:true,false,1,0,on,off',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        'es_fiscal' => 'nullable|in:true,false,1,0,on,off',
        'id_marcador' => 'nullable|exists:marcadores,id',
        'total_factura' => 'required|numeric|min:0.01',
        'fecha_factura' => 'nullable|date',
        'numero_factura' => 'nullable|string|max:50',
        'nota' => 'nullable|string',
        'referencia' => 'nullable|string|max:100',
        'id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
        'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        'uuid_factura' => 'nullable|string|max:50',
        'serie_factura' => 'nullable|string|max:20',
        'folio_factura' => 'nullable|string|max:20',
        'modo_iva' => 'nullable|in:CON_IVA,SIN_IVA',
        'monto_directo' => 'nullable|numeric|min:0',
        'ivas' => 'nullable|array|min:1|max:2',
        'ivas.*.id' => 'nullable|exists:tipos_iva,id',
        'ivas.*.monto' => 'numeric|min:0',
    ];

    $messages = [
        'fecha_poliza.required' => 'La fecha de póliza es obligatoria',
        'fecha_poliza.date' => 'La fecha de póliza debe ser una fecha válida',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        \Log::error('Validación store fallida:', $validator->errors()->toArray());
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('error', $validator->errors()->first());
    }

    try {
        DB::beginTransaction();

        // ============================================================
        // 🔥 VALIDACIÓN DE NATURALEZA DE LA CUENTA SEGÚN TIPO DE PÓLIZA
        // ============================================================
        $cuenta = Cuenta::find($request->id_cuenta);
        if (!$cuenta || !$cuenta->en_uso) {
            throw new \Exception('Cuenta no encontrada o inactiva');
        }

        $tipoPoliza = $request->tipo_poliza;
        $esPorPagar = filter_var($request->es_por_pagar, FILTER_VALIDATE_BOOLEAN);

        // ✅ Validar según el tipo de póliza
        if ($tipoPoliza === 'EGRESO') {
            // Para EGRESO, la cuenta debe poder recibir DÉBITOS
            if (!$cuenta->puedeRecibirDebito()) {
                $naturalezaTexto = $cuenta->Naturaleza ?? 'sin naturaleza definida';
                $nombreCuenta = $cuenta->nombre_cuenta;
                
                if ($cuenta->es_cuenta_resultados) {
                    throw new \Exception("La cuenta de resultados '{$nombreCuenta}' tiene naturaleza '{$naturalezaTexto}'. Las cuentas de resultados pueden recibir ambos movimientos, pero para EGRESOS se recomienda usar cuentas DEUDORAS.");
                }
                
                throw new \Exception("La cuenta '{$nombreCuenta}' tiene naturaleza '{$naturalezaTexto}' y NO puede recibir EGRESOS (débitos). Solo cuentas DEUDORAS o sin naturaleza pueden recibir egresos.");
            }
        } elseif ($tipoPoliza === 'INGRESO') {
            // Para INGRESO, la cuenta debe poder recibir CRÉDITOS
            if (!$cuenta->puedeRecibirCredito()) {
                $naturalezaTexto = $cuenta->Naturaleza ?? 'sin naturaleza definida';
                $nombreCuenta = $cuenta->nombre_cuenta;
                
                if ($cuenta->es_cuenta_resultados) {
                    throw new \Exception("La cuenta de resultados '{$nombreCuenta}' tiene naturaleza '{$naturalezaTexto}'. Las cuentas de resultados pueden recibir ambos movimientos, pero para INGRESOS se recomienda usar cuentas ACREEDORAS.");
                }
                
                throw new \Exception("La cuenta '{$nombreCuenta}' tiene naturaleza '{$naturalezaTexto}' y NO puede recibir INGRESOS (créditos). Solo cuentas ACREEDORAS o sin naturaleza pueden recibir ingresos.");
            }
        }

        // ✅ Validar cuenta fondeadora
        $cuentaFondeadora = Cuenta::find($request->id_cuenta_fondeadora);
        if (!$cuentaFondeadora || !$cuentaFondeadora->en_uso) {
            throw new \Exception('Cuenta fondeadora no encontrada o inactiva');
        }

        // Si es EGRESO y NO es por pagar, validar saldo
        if ($tipoPoliza === 'EGRESO' && !$esPorPagar) {
            $saldoFondeadora = (float) ($cuentaFondeadora->saldo_inicial ?? 0);
            $montoTotal = (float) $request->total_factura;
            
            // Obtener el monto total con IVA
            $modoIva = $request->modo_iva ?? 'CON_IVA';
            $totalConIva = $montoTotal;
            
            if ($modoIva === 'CON_IVA') {
                $ivas = $request->input('ivas', []);
                $totalIva = 0;
                foreach ($ivas as $ivaData) {
                    if (isset($ivaData['id']) && isset($ivaData['monto'])) {
                        $tipoIva = TipoIva::find($ivaData['id']);
                        if ($tipoIva) {
                            $totalIva += round($ivaData['monto'] * ($tipoIva->porcentaje / 100), 2);
                        }
                    }
                }
                $totalConIva = $montoTotal + $totalIva;
            }
            
            if ($saldoFondeadora < $totalConIva) {
                throw new \Exception("Saldo insuficiente en la cuenta fondeadora '{$cuentaFondeadora->nombre_cuenta}'. Disponible: $" . number_format($saldoFondeadora, 2) . ", Necesario: $" . number_format($totalConIva, 2));
            }
        }

        $empresaId = session('empresa_movimientos');
        
        if (!$empresaId) {
            $empresa = auth()->user()->empresas()->first();
            $empresaId = $empresa ? $empresa->id : null;
        }

        if (!$empresaId) {
            throw new \Exception('No se pudo determinar la empresa para la póliza');
        }

        $esFiscal = filter_var($request->es_fiscal, FILTER_VALIDATE_BOOLEAN);
        $categoria = $esFiscal ? 'FISCAL' : 'NO_FISCAL';
        $modoIva = $request->modo_iva ?? 'CON_IVA';

        $rutaPdf = null;
        $nombrePdf = null;
        $nombreXml = null;
        $rutaXml = null;

        if ($request->hasFile('pdf_file')) {
            $pdfFile = $request->file('pdf_file');
            if ($pdfFile->isValid()) {
                $nombrePdf = $pdfFile->getClientOriginalName();
                $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                \Log::info('PDF guardado:', ['ruta' => $rutaPdf]);
            }
        }


        $fechaPoliza = $request->fecha_poliza ? Carbon::parse($request->fecha_poliza) : now();

        $poliza = Poliza::create([
            'id_empresa' => $empresaId,
            'tipo_poliza' => $request->tipo_poliza,
            'fecha_poliza' => $fechaPoliza,
            'fecha_vencimiento' => $esPorPagar ? $request->fecha_vencimiento : null,
            'categoria' => $categoria,
            'estatus' => $esPorPagar ? 'PENDIENTE' : 'CAPTURADO',
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
        $ivas = $request->input('ivas', []);
        
        $totalBase = 0;
        $totalIva = 0;
        $ivaIds = [];
        $montoCero = 0;
        $montoDieciseis = 0;
        $ivaDieciseisCalc = 0;

        if ($modoIva === 'SIN_IVA') {
            $montoDirecto = (float) $request->monto_directo;
            $totalBase = $montoDirecto;
            $totalIva = 0;
            $montoTotal = $montoDirecto;
            
            \Log::info('Modo SIN IVA:', [
                'montoDirecto' => $montoDirecto,
                'totalBase' => $totalBase,
                'montoTotal' => $montoTotal
            ]);
        } else {
            foreach ($ivas as $ivaId => $ivaData) {
                $id = isset($ivaData['id']) ? $ivaData['id'] : $ivaId;
                $monto = isset($ivaData['monto']) ? (float) $ivaData['monto'] : 0;
                
                if ($id && $monto > 0) {
                    $tipoIva = TipoIva::find($id);
                    if ($tipoIva) {
                        $montoBase = round($monto, 2);
                        $montoIva = round($montoBase * ($tipoIva->porcentaje / 100), 2);
                        
                        $totalBase += $montoBase;
                        $totalIva += $montoIva;
                        $ivaIds[] = $id;

                        if ($tipoIva->porcentaje == 0) {
                            $montoCero += $montoBase;
                        } elseif ($tipoIva->porcentaje == 16) {
                            $montoDieciseis += $montoBase;
                            $ivaDieciseisCalc += $montoIva;
                        }
                    }
                }
            }
            
            $montoTotal = $totalBase;
        }

        $primerIvaId = !empty($ivaIds) ? $ivaIds[0] : null;

        $movimiento = MovimientoPoliza::create([
            'id_poliza' => $poliza->id,
            'id_cuenta' => $request->id_cuenta,
            'id_caja_fondo' => $request->id_cuenta_fondeadora,
            'id_tipo_iva' => $primerIvaId,
            'monto' => round($montoTotal * $signo, 2),
            'monto_base' => round($totalBase * $signo, 2),
            'monto_iva' => round($totalIva * $signo, 2),
            'monto_traspaso' => null,
            'monto_iva_cero' => round($montoCero * $signo, 2),
            'monto_iva_dieciseis' => round($montoDieciseis * $signo, 2),
            'iva_dieciseis' => round($ivaDieciseisCalc * $signo, 2),
        ]);

        \Log::info('Movimiento creado:', [
            'monto' => $movimiento->monto,
            'monto_base' => $movimiento->monto_base,
            'monto_iva' => $movimiento->monto_iva,
            'modo_iva' => $modoIva,
            'fecha_poliza' => $fechaPoliza->toDateString(),
            'cuenta_usada' => $cuenta->nombre_cuenta,
            'naturaleza_cuenta' => $cuenta->Naturaleza,
            'tipo_poliza' => $tipoPoliza
        ]);

        if (!$esPorPagar) {
            if ($request->id_cuenta_fondeadora) {
                $this->actualizarSaldoCuenta($request->id_cuenta_fondeadora);
            }
            
            if ($request->id_cuenta && $request->id_cuenta != $request->id_cuenta_fondeadora) {
                $this->actualizarSaldoCuenta($request->id_cuenta);
            }
        }

        DB::commit();

        $mensaje = 'Póliza creada exitosamente. Folio: ' . $poliza->folio . ' | Fecha: ' . $fechaPoliza->format('d/m/Y') . ' | Estado: ' . $this->getEstatusTexto($poliza->estatus);
        $mensaje .= ' | Total: $' . number_format($totalBase, 2) . ' | IVA: $' . number_format($totalIva, 2);
        
        // 🔥 Mostrar información de la naturaleza
        $naturalezaTexto = $cuenta->Naturaleza ?? 'Sin naturaleza';
        $mensaje .= " | Cuenta: {$cuenta->nombre_cuenta} ({$naturalezaTexto})";
        
        if ($modoIva === 'SIN_IVA') {
            $mensaje .= ' (Sin IVA)';
        }
        
        if ($esPorPagar) {
            $mensaje .= ' (Póliza diferida - no afecta saldos hasta que se liquide)';
        }
        if ($esFiscal) {
            $mensaje .= ' Documentos fiscales: ';
            if ($rutaPdf) $mensaje .= 'PDF ✓ ';
            if ($nombreXml) $mensaje .= 'XML ✓';
        }

        return redirect()->route('movimientos.index')
            ->with('success', $mensaje);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
            ->withInput();
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
    // 🔧 FUNCIONES AUXILIARES
    // ============================================
    
    private function getMontoIvaPorPorcentaje($ivas, $porcentaje)
    {
        foreach ($ivas as $ivaData) {
            if (isset($ivaData['id'])) {
                $tipoIva = TipoIva::find($ivaData['id']);
                if ($tipoIva && $tipoIva->porcentaje == $porcentaje) {
                    return round($ivaData['monto'] ?? 0, 2);
                }
            }
        }
        return 0;
    }

    private function getIvaCalculadoPorPorcentaje($ivas, $porcentaje)
    {
        foreach ($ivas as $ivaData) {
            if (isset($ivaData['id'])) {
                $tipoIva = TipoIva::find($ivaData['id']);
                if ($tipoIva && $tipoIva->porcentaje == $porcentaje) {
                    $monto = round($ivaData['monto'] ?? 0, 2);
                    return round($monto * ($porcentaje / 100), 2);
                }
            }
        }
        return 0;
    }

    private function getEstatusTexto($estatus)
    {
        $map = [
            'CAPTURADO' => 'Capturado',
            'REVISADO' => 'Revisado',
            'AUTORIZADO' => 'Autorizado',
            'ABONADO' => 'Abonado',
            'LIQUIDADO' => 'Liquidado',
            'PENDIENTE' => 'Pendiente',
            'CERRADO' => 'Cerrado',
        ];
        return $map[$estatus] ?? $estatus;
    }

    private function actualizarEstatusPoliza($poliza)
    {
        $totalMovimientos = $poliza->movimientos()->sum('monto');
        $totalAbonado = $poliza->abonos()->sum('monto_abonado');

        if (in_array($poliza->estatus, ['CAPTURADO', 'REVISADO', 'AUTORIZADO', 'PENDIENTE'])) {
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

    /**
     * 🔧 ACTUALIZAR SALDO DE CUENTA - CORREGIDO
     */
    private function actualizarSaldoCuenta($idCuenta)
    {
        if (!$idCuenta) return 0;
        
        try {
            // 🔥 OBTENER TODOS LOS MOVIMIENTOS DE LA CUENTA (YA TIENEN EL SIGNO CORRECTO)
            $totalMonto = MovimientoPoliza::where('id_cuenta', $idCuenta)
                ->sum('monto');
            
            // 🔥 OBTENER MOVIMIENTOS DONDE ES CUENTA FONDEADORA (también tienen signo correcto)
            $totalFondeadora = MovimientoPoliza::where('id_caja_fondo', $idCuenta)
                ->sum('monto');
            
            // 🔥 SUMAR AMBOS CONCEPTOS (YA TIENEN EL SIGNO CORRECTO)
            $saldoTotal = $totalMonto + $totalFondeadora;
            
            // 🔥 ACTUALIZAR EL SALDO EN LA CUENTA
            $cuenta = Cuenta::find($idCuenta);
            if ($cuenta) {
                // Guardamos el saldo calculado
                $cuenta->saldo_inicial = $saldoTotal;
                $cuenta->save();
                
                \Log::info('Saldo actualizado:', [
                    'cuenta_id' => $idCuenta,
                    'cuenta_nombre' => $cuenta->nombre_cuenta,
                    'total_monto' => $totalMonto,
                    'total_fondeadora' => $totalFondeadora,
                    'saldo_total' => $saldoTotal
                ]);
            }
            
            return $saldoTotal;
        } catch (\Exception $e) {
            \Log::error('Error al actualizar saldo de cuenta:', [
                'cuenta_id' => $idCuenta,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
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
    // 🔄 STORE TRASPASO - CON VALIDACIÓN DE NATURALEZA
    // ============================================
    public function storeTraspaso(Request $request)
    {
        \Log::info('=== INICIO storeTraspaso ===');
        \Log::info('Datos recibidos:', $request->all());

        if (!Gate::allows('crear-movimientos')) {
            \Log::warning('Permiso denegado para crear traspasos');
            return back()->with('error', 'No tienes permiso para crear traspasos');
        }

        $rules = [
            'tipo_poliza' => 'required|in:TRASPASO',
            'id_cuenta_origen' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_destino',
            'id_cuenta_destino' => 'required|exists:cuentas,id_cuenta|different:id_cuenta_origen',
            'monto' => 'required|numeric|min:0.01',
            'es_fiscal' => 'nullable|in:true,false,1,0,on,off',
            'id_marcador' => 'nullable|exists:marcadores,id',
            'referencia' => 'nullable|string|max:100',
            'nota' => 'nullable|string',
            'fecha_factura' => 'nullable|date',
            'numero_factura' => 'nullable|string|max:50',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'uuid_factura' => 'nullable|string|max:50',
            'serie_factura' => 'nullable|string|max:20',
            'folio_factura' => 'nullable|string|max:20',
            'ivas' => 'nullable|array|min:1|max:2',
            'ivas.*.id' => 'nullable|exists:tipos_iva,id',
            'ivas.*.monto' => 'numeric|min:0',
        ];

        $esFiscal = filter_var($request->es_fiscal, FILTER_VALIDATE_BOOLEAN);
        if ($esFiscal) {
            $rules['pdf_file'] = 'required|file|mimes:pdf|max:10240';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Validación fallida:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // ============================================================
            // 🔥 VALIDAR CUENTAS DE ORIGEN Y DESTINO CON NATURALEZA
            // ============================================================
            $cuentaOrigen = Cuenta::find($request->id_cuenta_origen);
            if (!$cuentaOrigen || !$cuentaOrigen->en_uso) {
                throw new \Exception('Cuenta de origen no encontrada o inactiva');
            }

            $cuentaDestino = Cuenta::find($request->id_cuenta_destino);
            if (!$cuentaDestino || !$cuentaDestino->en_uso) {
                throw new \Exception('Cuenta de destino no encontrada o inactiva');
            }

            // ✅ Validar que la cuenta de origen pueda recibir débitos (salida de dinero)
            if (!$cuentaOrigen->puedeRecibirDebito()) {
                $naturalezaTexto = $cuentaOrigen->Naturaleza ?? 'sin naturaleza definida';
                throw new \Exception("La cuenta de origen '{$cuentaOrigen->nombre_cuenta}' tiene naturaleza '{$naturalezaTexto}' y NO puede ser origen de un traspaso (débito).");
            }

            // ✅ Validar que la cuenta de destino pueda recibir créditos (entrada de dinero)
            if (!$cuentaDestino->puedeRecibirCredito()) {
                $naturalezaTexto = $cuentaDestino->Naturaleza ?? 'sin naturaleza definida';
                throw new \Exception("La cuenta de destino '{$cuentaDestino->nombre_cuenta}' tiene naturaleza '{$naturalezaTexto}' y NO puede ser destino de un traspaso (crédito).");
            }

            $saldoOrigen = (float) ($cuentaOrigen->saldo_inicial ?? 0);
            $monto = round($request->monto, 2);

            if ($saldoOrigen < $monto) {
                return back()->with('error', "Saldo insuficiente en la cuenta origen. Disponible: $" . number_format($saldoOrigen, 2))->withInput();
            }

            $empresaId = session('empresa_movimientos');
            if (!$empresaId) {
                $empresa = auth()->user()->empresas()->first();
                $empresaId = $empresa ? $empresa->id : null;
            }

            if (!$empresaId) {
                throw new \Exception('No se pudo determinar la empresa');
            }

            $categoria = $esFiscal ? 'FISCAL' : 'NO_FISCAL';

            $rutaPdf = null;
            $nombrePdf = null;
            $nombreXml = null;
            $rutaXml = null;

            if ($esFiscal) {
                if ($request->hasFile('pdf_file')) {
                    $pdfFile = $request->file('pdf_file');
                    if ($pdfFile->isValid()) {
                        $nombrePdf = $pdfFile->getClientOriginalName();
                        $rutaPdf = $pdfFile->store('documentos_fiscales/pdfs', 'public');
                    }
                }

           
            }

            $poliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => 'TRASPASO',
                'fecha_poliza' => now(),
                'fecha_vencimiento' => null,
                'categoria' => $categoria,
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => false,
                'referencia' => $request->referencia ?? 'TRASPASO-' . date('YmdHis'),
                'nota' => $request->nota,
                'id_persona' => null,
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

            $poliza->refresh();

            $ivas = $request->input('ivas', []);
            $montoBaseTotal = 0;
            $montoIvaTotal = 0;
            $ivaIds = [];

            if (!empty($ivas)) {
                foreach ($ivas as $ivaData) {
                    if (isset($ivaData['id']) && isset($ivaData['monto']) && $ivaData['monto'] > 0) {
                        $tipoIva = TipoIva::find($ivaData['id']);
                        if ($tipoIva) {
                            $montoBase = round($ivaData['monto'], 2);
                            $montoIva = round($montoBase * ($tipoIva->porcentaje / 100), 2);
                            
                            $montoBaseTotal += $montoBase;
                            $montoIvaTotal += $montoIva;
                            $ivaIds[] = $ivaData['id'];
                        }
                    }
                }
            } else {
                $montoBaseTotal = $monto;
                $montoIvaTotal = 0;
            }

            $primerIvaId = !empty($ivaIds) ? $ivaIds[0] : null;

            // Movimiento de salida (origen) - DÉBITO
            MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta_origen,
                'id_caja_fondo' => null,
                'id_tipo_iva' => $primerIvaId,
                'monto' => -$monto,
                'monto_base' => -$montoBaseTotal,
                'monto_iva' => -$montoIvaTotal,
                'monto_traspaso' => null,
                'monto_iva_cero' => -$this->getMontoIvaPorPorcentaje($ivas, 0),
                'monto_iva_dieciseis' => -$this->getMontoIvaPorPorcentaje($ivas, 16),
                'iva_dieciseis' => -$this->getIvaCalculadoPorPorcentaje($ivas, 16),
            ]);

            // Movimiento de entrada (destino) - CRÉDITO
            MovimientoPoliza::create([
                'id_poliza' => $poliza->id,
                'id_cuenta' => $request->id_cuenta_destino,
                'id_caja_fondo' => null,
                'id_tipo_iva' => $primerIvaId,
                'monto' => $monto,
                'monto_base' => $montoBaseTotal,
                'monto_iva' => $montoIvaTotal,
                'monto_traspaso' => null,
                'monto_iva_cero' => $this->getMontoIvaPorPorcentaje($ivas, 0),
                'monto_iva_dieciseis' => $this->getMontoIvaPorPorcentaje($ivas, 16),
                'iva_dieciseis' => $this->getIvaCalculadoPorPorcentaje($ivas, 16),
            ]);

            $this->actualizarSaldoCuenta($request->id_cuenta_origen);
            $this->actualizarSaldoCuenta($request->id_cuenta_destino);

            DB::commit();

            // 🔥 Mensaje con información de las cuentas
            $mensaje = "Traspaso creado exitosamente. Folio: {$poliza->folio} | Estado: CAPTURADO";
            $mensaje .= " | Origen: {$cuentaOrigen->nombre_cuenta} -> Destino: {$cuentaDestino->nombre_cuenta}";
            $mensaje .= " | Monto: $" . number_format($monto, 2);
            
            if ($esFiscal) {
                $mensaje .= ' Documentos fiscales: ';
                if ($rutaPdf) $mensaje .= 'PDF ✓ ';
                if ($nombreXml) $mensaje .= 'XML ✓ ';
            }

            return redirect()->route('movimientos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en storeTraspaso:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al crear el traspaso: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================
    // 📄 SHOW - MOSTRAR MOVIMIENTO
    // ============================================
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
            'poliza.archivos',
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
            'poliza.abonos'
        ])->find($id);

        if (!$movimiento) {
            return redirect()->route('movimientos.index')
                ->with('error', 'Movimiento no encontrado');
        }

        $abonos = $movimiento->poliza->abonos ?? collect();
        $totalAbonado = $abonos->sum('monto_abonado');
        
        $tipoPoliza = $movimiento->poliza->tipo_poliza;
        $esTraspaso = $tipoPoliza === 'TRASPASO';
        $esIngresoEgreso = in_array($tipoPoliza, ['INGRESO', 'EGRESO']);
        $esFiscal = $movimiento->poliza->categoria === 'FISCAL';
        $esPorPagar = $movimiento->poliza->es_por_pagar ?? false;

        if ($esTraspaso) {
            $montoMostrar = $movimiento->monto_traspaso ?? abs($movimiento->monto);
            $saldoPendiente = $montoMostrar - $totalAbonado;
        } else {
            $montoMostrar = $movimiento->monto;
            $saldoPendiente = abs($montoMostrar) - $totalAbonado;
        }

        $pdfUrl = null;
        if ($esFiscal && !empty($movimiento->poliza->ruta_pdf)) {
            $pdfUrl = route('movimientos.documento.fiscal', [
                'id' => $movimiento->id_poliza,
                'tipo' => 'pdf'
            ]);
        }

        $xmlUrl = null;
        if ($esFiscal && !empty($movimiento->poliza->ruta_xml)) {
            $xmlUrl = route('movimientos.documento.fiscal', [
                'id' => $movimiento->id_poliza,
                'tipo' => 'xml'
            ]);
        }

        $montoIvaCero = $movimiento->monto_iva_cero ?? 0;
        $montoIvaDieciseis = $movimiento->monto_iva_dieciseis ?? 0;
        $ivaDieciseis = $movimiento->iva_dieciseis ?? 0;
        $tieneDobleIva = ($montoIvaCero != 0 || $montoIvaDieciseis != 0 || $ivaDieciseis != 0);

        $cuentaOrigen = null;
        $cuentaDestino = null;
        $montoTraspaso = null;
        $cuentaOrigenId = null;
        $cuentaDestinoId = null;
        
        if ($esTraspaso) {
            $movimientosTraspaso = MovimientoPoliza::where('id_poliza', $movimiento->id_poliza)->get();
            
            $movOrigen = $movimientosTraspaso->firstWhere(function($m) {
                return $m->monto < 0;
            });
            
            $movDestino = $movimientosTraspaso->firstWhere(function($m) {
                return $m->monto > 0;
            });
            
            if ($movOrigen) {
                $cuentaOrigenId = $movOrigen->id_cuenta;
                if ($movOrigen->cuenta) {
                    $cuentaOrigen = $movOrigen->cuenta->nombre_cuenta;
                }
            }
            
            if ($movDestino) {
                $cuentaDestinoId = $movDestino->id_cuenta;
                if ($movDestino->cuenta) {
                    $cuentaDestino = $movDestino->cuenta->nombre_cuenta;
                }
            }
            
            $montoTraspaso = $movimiento->monto_traspaso ?? abs($movimiento->monto);
        }

        $user = auth()->user();
        
        $esSuperUsuario = $user->esSuperUsuario();
        $esAdministrador = $user->esAdministrador();
        $esAuditor = $user->esAuditor();
        $esCapturista = $user->esCapturista();
        $esLector = $user->esLector();
        
        $estatus = $movimiento->poliza->estatus;
        
        $permisos = [
            // ============================================
            // 🔥 PERMISOS PARA EL MENÚ (APP LAYOUT)
            // ============================================
            'puede_ver' => true,
            'puede_ver_personas' => $esAdministrador || $esSuperUsuario || $esAuditor || $esCapturista,
            'puede_ver_cuentas' => $esAdministrador || $esSuperUsuario || $esAuditor || $esCapturista,
            'puede_ver_usuarios' => $esAdministrador || $esSuperUsuario || $esAuditor,
            'puede_ver_empresas' => $esSuperUsuario,
            'puede_ver_reportes' => $esAdministrador || $esSuperUsuario || $esAuditor,
            
            // ============================================
            // 🔥 PERMISOS PARA LA VISTA SHOW
            // ============================================
            'puede_ver_pdf' => true,
            'puede_revisar' => ($esAdministrador || $esSuperUsuario) && $estatus === 'CAPTURADO',
            'puede_autorizar' => ($esAuditor || $esSuperUsuario) && $estatus === 'REVISADO',
            'puede_cerrar' => ($esCapturista || $esAdministrador || $esSuperUsuario) && 
                            !$esAuditor && 
                            !$esLector &&
                            !in_array($estatus, ['LIQUIDADO', 'AUTORIZADO', 'CERRADO']),
            'puede_reabrir' => ($esAdministrador || $esSuperUsuario) && $estatus === 'CERRADO',
            'puede_editar' => !$esAuditor && 
                            !$esLector && 
                            ($esSuperUsuario || ($esAdministrador && $estatus === 'CAPTURADO') || ($esCapturista && $estatus === 'CAPTURADO')),
            'puede_eliminar' => $esSuperUsuario,
            'puede_regresar' => true,
            
            // ============================================
            // 🔥 ROLES PARA REFERENCIA
            // ============================================
            'rol' => $user->tipo_usuario_texto ?? 'Sin rol',
            'es_super_usuario' => $esSuperUsuario,
            'es_administrador' => $esAdministrador,
            'es_auditor' => $esAuditor,
            'es_capturista' => $esCapturista,
            'es_lector' => $esLector,
        ];

        $movimientoData = [
            'id' => $movimiento->id,
            'id_movimiento' => $movimiento->id,
            'id_poliza' => $movimiento->id_poliza,
            'folio' => $movimiento->poliza->folio,
            'tipo_poliza' => $tipoPoliza,
            'fecha_poliza' => $movimiento->poliza->fecha_poliza,
            'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento,
            'categoria' => $movimiento->poliza->categoria,
            'estatus' => $estatus,
            'estatus_texto' => $this->getEstatusTexto($estatus),
            'es_por_pagar' => $esPorPagar,
            'referencia' => $movimiento->poliza->referencia,
            'nota' => $movimiento->poliza->nota,
            'es_ingreso_egreso' => $esIngresoEgreso,
            'es_traspaso' => $esTraspaso,
            'es_fiscal' => $esFiscal,
            'tiene_doble_iva' => $tieneDobleIva,
            'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : null,
            'persona_id' => $movimiento->poliza->id_persona,
            'cuenta' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->cuenta->nombre_cuenta : null,
            'cuenta_id' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->id_cuenta : null,
            'cuenta_fondeadora' => $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->cuentaFondeadora->nombre_cuenta : null,
            'cuenta_fondeadora_id' => $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->id_caja_fondo : null,
            'cuenta_origen' => $cuentaOrigen,
            'cuenta_origen_id' => $cuentaOrigenId,
            'cuenta_destino' => $cuentaDestino,
            'cuenta_destino_id' => $cuentaDestinoId,
            'monto_traspaso' => $montoTraspaso,
            'monto' => $montoMostrar,
            'monto_abs' => abs($montoMostrar),
            'monto_base' => $movimiento->monto_base,
            'monto_iva' => $movimiento->monto_iva,
            'monto_iva_cero' => $montoIvaCero,
            'monto_iva_dieciseis' => $montoIvaDieciseis,
            'iva_dieciseis' => $ivaDieciseis,
            'nombre_pdf' => $movimiento->poliza->nombre_pdf,
            'nombre_xml' => $movimiento->poliza->nombre_xml,
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
            'marcador' => $movimiento->poliza->marcador ? $movimiento->poliza->marcador->nombre_marcador : null,
            'marcador_id' => $movimiento->poliza->id_marcador,
            'usuario' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_usuario : null,
            'usuario_nombre' => $movimiento->poliza->usuarioCreador ? $movimiento->poliza->usuarioCreador->nombre_completo : null,
            'usuario_id' => $movimiento->poliza->id_usuario_creador,
            'usuario_revisor' => $movimiento->poliza->usuarioRevisor ? $movimiento->poliza->usuarioRevisor->nombre_usuario : null,
            'usuario_revisor_id' => $movimiento->poliza->id_usuario_revisor,
            'usuario_autorizador' => $movimiento->poliza->usuarioAutorizador ? $movimiento->poliza->usuarioAutorizador->nombre_usuario : null,
            'usuario_autorizador_id' => $movimiento->poliza->id_usuario_autorizador,
            'fecha_revision' => $movimiento->poliza->fecha_revision,
            'fecha_autorizacion' => $movimiento->poliza->fecha_autorizacion,
            'fecha_factura' => $movimiento->poliza->fecha_factura,
            'numero_factura' => $movimiento->poliza->numero_factura,
            'fecha_creacion' => $movimiento->created_at,
            'fecha_actualizacion' => $movimiento->updated_at,
            'pdf_url' => $pdfUrl,
            'xml_url' => $xmlUrl,
            'tiene_pdf_fiscal' => !empty($movimiento->poliza->ruta_pdf),
            'tiene_xml_fiscal' => !empty($movimiento->poliza->ruta_xml),
            'uuid_factura' => $movimiento->poliza->uuid_factura,
            'serie_factura' => $movimiento->poliza->serie_factura,
            'folio_factura' => $movimiento->poliza->folio_factura,
            'comentario_revision' => $movimiento->poliza->comentario_revision,
            'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
            'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
            'archivos_adjuntos' => $movimiento->poliza->archivos->map(function($archivo) {
                return [
                    'id' => $archivo->id,
                    'nombre_original' => $archivo->nombre_original,
                    'tipo_archivo' => $archivo->tipo_archivo,
                    'tamano' => $archivo->tamano,
                    'mime_type' => $archivo->mime_type,
                    'created_at' => $archivo->created_at,
                    'url' => route('movimientos.archivos.ver', $archivo->id)
                ];
            }),
        ];

        return Inertia::render('Movimientos/Show', [
            'movimiento' => $movimientoData,
            'permisos' => $permisos,
        ]);
    }

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
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
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

        $cuentaFondeadoraId = $movimiento->id_caja_fondo;
        $cuentaFondeadoraNombre = $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso 
            ? $movimiento->cuentaFondeadora->nombre_cuenta 
            : null;

        // ============================================
        // 🔥 RECUPERAR IVAS DE LA PÓLIZA ORIGINAL
        // ============================================
        $ivasParaAbono = [];

        // 🔥 Verificar si la póliza tiene IVA 0%
        $montoIvaCero = abs((float) ($movimiento->monto_iva_cero ?? 0));
        if ($montoIvaCero > 0) {
            $ivaCero = TipoIva::where('porcentaje', 0)->where('activo', true)->first();
            if ($ivaCero) {
                $ivasParaAbono[] = [
                    'id' => $ivaCero->id,
                    'monto' => 0, // El usuario ingresará el monto
                    'porcentaje' => 0
                ];
            }
        }

        // 🔥 Verificar si la póliza tiene IVA 16%
        $montoIvaDieciseis = abs((float) ($movimiento->monto_iva_dieciseis ?? 0));
        $ivaDieciseisCalc = abs((float) ($movimiento->iva_dieciseis ?? 0));
        
        if ($montoIvaDieciseis > 0 || $ivaDieciseisCalc > 0) {
            $ivaDieciseisModel = TipoIva::where('porcentaje', 16)->where('activo', true)->first();
            if ($ivaDieciseisModel) {
                $ivasParaAbono[] = [
                    'id' => $ivaDieciseisModel->id,
                    'monto' => 0, // El usuario ingresará el monto
                    'porcentaje' => 16
                ];
            }
        }

        // 🔥 Si no se encontraron IVAs por porcentaje, intentar por id_tipo_iva
        if (empty($ivasParaAbono) && isset($movimiento->id_tipo_iva) && $movimiento->id_tipo_iva) {
            $iva = TipoIva::find($movimiento->id_tipo_iva);
            if ($iva) {
                $ivasParaAbono[] = [
                    'id' => $iva->id,
                    'monto' => 0,
                    'porcentaje' => $iva->porcentaje
                ];
            }
        }

        // 🔥 Si aún no hay IVAs, seleccionar el IVA por defecto (16%)
        if (empty($ivasParaAbono)) {
            $ivaDefecto = TipoIva::where('porcentaje', 16)->where('activo', true)->first();
            if ($ivaDefecto) {
                $ivasParaAbono[] = [
                    'id' => $ivaDefecto->id,
                    'monto' => 0,
                    'porcentaje' => 16
                ];
            }
        }

        // ============================================
        // 🔥 OBTENER TIPOS DE IVA (TODOS LOS ACTIVOS)
        // ============================================
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

        return Inertia::render('Movimientos/Abono', [
            'movimiento' => [
                'id_movimiento' => $movimiento->id,
                'id_poliza' => $movimiento->id_poliza,
                'referencia' => $movimiento->poliza->folio ?? 'S/N',
                'tipo_poliza' => $movimiento->poliza->tipo_poliza,
                'fecha_poliza' => $movimiento->poliza->fecha_poliza?->format('Y-m-d'),
                'fecha_vencimiento' => $movimiento->poliza->fecha_vencimiento?->format('Y-m-d'),
                'persona' => $movimiento->poliza->persona ? $movimiento->poliza->persona->nombre_completo : 'N/A',
                'cuenta' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                'cuenta_fondeadora' => $cuentaFondeadoraNombre,
                'cuenta_fondeadora_id' => $cuentaFondeadoraId,
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
                'es_por_pagar' => $movimiento->poliza->es_por_pagar,
                // 🔥 IVAS QUE DEBE HEREDAR EL ABONO
                'ivas_heredados' => $ivasParaAbono,
            ],
            'tipos_iva' => $tiposIva
        ]);
    }

public function edit(string $id)
{
    if (!Gate::allows('editar-movimientos')) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No tienes permiso para editar movimientos');
    }

    // 🔥 OBTENER EMPRESA DE SESIÓN
    $empresaId = session('empresa_movimientos') ?? session('empresa_seleccionada');
    
    if (!$empresaId) {
        $empresa = auth()->user()->empresas()->first();
        $empresaId = $empresa ? $empresa->id : null;
    }

    if (!$empresaId) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No tienes una empresa seleccionada.');
    }

    session(['empresa_movimientos' => $empresaId]);
    session(['empresa_seleccionada' => $empresaId]);

    // 🔥 OBTENER EL MOVIMIENTO CON TODAS LAS RELACIONES
    $movimiento = MovimientoPoliza::with([
        'poliza.persona',
        'poliza.usuarioCreador',
        'poliza.usuarioRevisor',
        'poliza.usuarioAutorizador',
        'poliza.marcador',
        'cuenta' => function($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId)->where('en_uso', true);
        },
        'cuentaFondeadora' => function($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId)->where('en_uso', true);
        }
    ])->find($id);

    if (!$movimiento) {
        return redirect()->route('movimientos.index')
            ->with('error', 'Movimiento no encontrado');
    }

    // Validar estado editable - PERMITIR editar pólizas en estado PENDIENTE (diferidas)
    if (in_array($movimiento->poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
        return redirect()->route('movimientos.index')
            ->with('error', 'No se puede editar una póliza en estado ' . $this->getEstatusTexto($movimiento->poliza->estatus));
    }

    // ============================================
    // 🔥 RECUPERAR IVAS DE LA PÓLIZA - CORREGIDO
    // Los IVAs NO están en poliza->iva_id, sino en los campos de MovimientoPoliza
    // ============================================
    $ivasAsociados = [];

    // 🔥 Convertir valores a números absolutos (positivos) para el frontend
    $montoIvaCero = abs((float) ($movimiento->monto_iva_cero ?? 0));
    $montoIvaDieciseis = abs((float) ($movimiento->monto_iva_dieciseis ?? 0));
    $ivaDieciseis = abs((float) ($movimiento->iva_dieciseis ?? 0));
    $montoIva = abs((float) ($movimiento->monto_iva ?? 0));

    // 🔥 Buscar los tipos de IVA por porcentaje
    $ivaCero = TipoIva::where('porcentaje', 0)->where('activo', true)->first();
    $ivaDieciseisModel = TipoIva::where('porcentaje', 16)->where('activo', true)->first();

    // 🔥 IVA 0% - si hay monto_iva_cero > 0
    if ($montoIvaCero > 0 && $ivaCero) {
        $ivasAsociados[] = [
            'id' => $ivaCero->id,
            'monto' => $montoIvaCero
        ];
    }

    // 🔥 IVA 16% - si hay monto_iva_dieciseis > 0
    if ($montoIvaDieciseis > 0 && $ivaDieciseisModel) {
        $ivasAsociados[] = [
            'id' => $ivaDieciseisModel->id,
            'monto' => $montoIvaDieciseis
        ];
    }

    // 🔥 Si NO se encontraron por los campos específicos, intentar por id_tipo_iva
    if (empty($ivasAsociados) && isset($movimiento->id_tipo_iva) && $movimiento->id_tipo_iva) {
        $iva = TipoIva::find($movimiento->id_tipo_iva);
        if ($iva && $montoIva > 0) {
            $ivasAsociados[] = [
                'id' => $iva->id,
                'monto' => $montoIva
            ];
        }
    }

    // 🔥 DETERMINAR SI ES TRASPASO
    $esTraspaso = $movimiento->poliza->tipo_poliza === 'TRASPASO';

    // 🔥 OBTENER DATOS DE TRASPASO
    $cuentaOrigenId = null;
    $cuentaDestinoId = null;
    if ($esTraspaso) {
        $movimientosTraspaso = MovimientoPoliza::where('id_poliza', $movimiento->id_poliza)->get();
        $movimientoOrigen = $movimientosTraspaso->firstWhere('monto', '<', 0);
        $movimientoDestino = $movimientosTraspaso->firstWhere('monto', '>', 0);
        $cuentaOrigenId = $movimientoOrigen ? $movimientoOrigen->id_cuenta : null;
        $cuentaDestinoId = $movimientoDestino ? $movimientoDestino->id_cuenta : null;
    }

    // ============================================
    // 🔥 CONSTRUIR OBJETO MOVIMIENTO COMPLETO
    // ============================================
    $movimientoData = [
        'id' => $movimiento->id,
        'id_movimiento' => $movimiento->id,
        'id_poliza' => $movimiento->id_poliza,
        'tipo_poliza' => $movimiento->poliza->tipo_poliza,
        'fecha_poliza' => $movimiento->poliza->fecha_poliza?->format('Y-m-d'),
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
        'monto_abs' => abs($movimiento->monto),
        'id_tipo_iva' => $movimiento->id_tipo_iva,
        'monto_base' => abs($movimiento->monto_base ?? 0),
        'monto_iva' => abs($movimiento->monto_iva ?? 0),
        // 🔥 DATOS FISCALES
        'fecha_factura' => $movimiento->poliza->fecha_factura?->format('Y-m-d'),
        'numero_factura' => $movimiento->poliza->numero_factura,
        'serie_factura' => $movimiento->poliza->serie_factura,
        'folio_factura' => $movimiento->poliza->folio_factura,
        'uuid_factura' => $movimiento->poliza->uuid_factura,
        'pdf_existente' => !empty($movimiento->poliza->ruta_pdf),
        'xml_existente' => !empty($movimiento->poliza->ruta_xml),
        'nombre_pdf' => $movimiento->poliza->nombre_pdf,
        'nombre_xml' => $movimiento->poliza->nombre_xml,
        // 🔥 IVAS ASOCIADOS (para precargar en el frontend)
        'ivas' => $ivasAsociados,
        // 🔥 DATOS DE TRASPASO
        'id_cuenta_origen' => $cuentaOrigenId,
        'id_cuenta_destino' => $cuentaDestinoId,
        'cuenta_origen_nombre' => $cuentaOrigenId ? Cuenta::find($cuentaOrigenId)?->nombre_cuenta : null,
        'cuenta_destino_nombre' => $cuentaDestinoId ? Cuenta::find($cuentaDestinoId)?->nombre_cuenta : null,
        // 🔥 OTROS DATOS
        'nota' => $movimiento->poliza->nota,
        'referencia' => $movimiento->poliza->referencia,
        'folio' => $movimiento->poliza->folio,
        'estatus' => $movimiento->poliza->estatus,
        'estatus_texto' => $this->getEstatusTexto($movimiento->poliza->estatus),
        'comentario_revision' => $movimiento->poliza->comentario_revision,
        'comentario_autorizacion' => $movimiento->poliza->comentario_autorizacion,
        'motivo_rechazo' => $movimiento->poliza->motivo_rechazo,
        'monto_traspaso' => $movimiento->monto_traspaso ?? 0,
        // 🔥 CAMPOS DE IVA DIRECTOS (para depuración)
        'monto_iva_cero' => $montoIvaCero,
        'monto_iva_dieciseis' => $montoIvaDieciseis,
        'iva_dieciseis' => $ivaDieciseis,
    ];

    // ============================================
    // 🔥 CUENTAS FONDEADORAS
    // ============================================
    $cuentasFondeadoras = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where('fondeo_c', 1)
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo' => (float) ($cuenta->saldo_inicial ?? 0),
                'Naturaleza' => $cuenta->Naturaleza,
                'tipo' => 'fondeadora'
            ];
        });

    // ============================================
    // 🔥 CUENTAS PARA EGRESOS
    // ============================================
    $cuentasEgreso = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where(function($query) {
            $query->where('fondeo_c', '!=', 1)
                  ->orWhereNull('fondeo_c');
        })
        ->where(function($query) {
            $query->where('es_cuenta_resultados', true)
                  ->orWhereNull('Naturaleza')
                  ->orWhere('Naturaleza', 'DEUDORA');
        })
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'Naturaleza' => $cuenta->Naturaleza ?? 'SIN NATURALEZA',
                'es_cuenta_resultados' => (bool) $cuenta->es_cuenta_resultados,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0),
                'tipo_movimiento' => 'egreso'
            ];
        });

    // ============================================
    // 🔥 CUENTAS PARA INGRESOS
    // ============================================
    $cuentasIngreso = Cuenta::where('id_empresa', $empresaId)
        ->where('en_uso', true)
        ->where(function($query) {
            $query->where('fondeo_c', '!=', 1)
                  ->orWhereNull('fondeo_c');
        })
        ->where(function($query) {
            $query->where('es_cuenta_resultados', true)
                  ->orWhereNull('Naturaleza')
                  ->orWhere('Naturaleza', 'ACREEDORA');
        })
        ->orderBy('nombre_cuenta')
        ->get()
        ->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'Naturaleza' => $cuenta->Naturaleza ?? 'SIN NATURALEZA',
                'es_cuenta_resultados' => (bool) $cuenta->es_cuenta_resultados,
                'fondeo_c' => (int) ($cuenta->fondeo_c ?? 0),
                'saldo_inicial' => (float) ($cuenta->saldo_inicial ?? 0),
                'tipo_movimiento' => 'ingreso'
            ];
        });

    // ============================================
    // 🔥 PERSONAS
    // ============================================
    $personas = \App\Models\Persona::where('activo', true)
        ->orderBy('Nombre')
        ->get(['id_persona', 'Nombre', 'Paterno', 'Materno'])
        ->map(function($persona) {
            $nombreCompleto = $persona->Nombre;
            if ($persona->Paterno) {
                $nombreCompleto .= ' ' . $persona->Paterno;
            }
            if ($persona->Materno) {
                $nombreCompleto .= ' ' . $persona->Materno;
            }
            return [
                'id_persona' => $persona->id_persona,
                'nombre_completo' => $nombreCompleto,
                'nombre' => $persona->Nombre,
                'paterno' => $persona->Paterno,
                'materno' => $persona->Materno
            ];
        });

    // ============================================
    // 🔥 MARCADORES
    // ============================================
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

    // ============================================
    // 🔥 TIPOS DE IVA
    // ============================================
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

    // ============================================
    // 🔥 RETORNAR
    // ============================================
    return Inertia::render('Movimientos/Edit', [
        'movimiento' => $movimientoData,
        'empresa_id' => (int) $empresaId,
        'cuentas_egreso' => $cuentasEgreso,
        'cuentas_ingreso' => $cuentasIngreso,
        'cuentas_fondeadoras' => $cuentasFondeadoras,
        'tipos_iva' => $tiposIva,
        'marcadores' => $marcadores,
        'personas' => $personas,
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

            if (!$poliza->es_por_pagar) {
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
    // 📄 DESTROY - ELIMINAR MOVIMIENTO
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
            $esTraspaso = $poliza->tipo_poliza === 'TRASPASO';
            $movimientosPoliza = MovimientoPoliza::where('id_poliza', $poliza->id)->get();
            
            if (in_array($poliza->estatus, ['AUTORIZADO', 'ABONADO', 'LIQUIDADO'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar movimientos de una póliza en estado ' . $this->getEstatusTexto($poliza->estatus)
                ], 422);
            }

            $cuentasAActualizar = [];
            
            foreach ($movimientosPoliza as $mov) {
                if ($mov->id_cuenta) {
                    $cuentasAActualizar[] = $mov->id_cuenta;
                }
                if ($mov->id_caja_fondo) {
                    $cuentasAActualizar[] = $mov->id_caja_fondo;
                }
            }
            
            AbonoPoliza::where('id_poliza', $poliza->id)->delete();

            if ($poliza->ruta_pdf && Storage::disk('public')->exists($poliza->ruta_pdf)) {
                Storage::disk('public')->delete($poliza->ruta_pdf);
            }
            if ($poliza->ruta_xml && Storage::disk('public')->exists($poliza->ruta_xml)) {
                Storage::disk('public')->delete($poliza->ruta_xml);
            }

            foreach ($movimientosPoliza as $mov) {
                $mov->delete();
            }

            $poliza->delete();

            if (!$poliza->es_por_pagar) {
                foreach (array_unique($cuentasAActualizar) as $idCuenta) {
                    if ($idCuenta) {
                        $this->actualizarSaldoCuenta($idCuenta);
                    }
                }
            }

            DB::commit();

            $mensaje = 'Movimiento eliminado exitosamente';
            if ($esTraspaso) {
                $mensaje = 'Traspaso eliminado exitosamente. Los saldos de las cuentas han sido restaurados.';
            }
            if ($poliza->es_por_pagar) {
                $mensaje .= ' (Póliza diferida - no se afectaron saldos)';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 📄 STORE ABONO - CORREGIDO CON SUMA/RESTA DIRECTA
    // ============================================
    public function storeAbono(Request $request)
    {
        \Log::info('=== INICIO storeAbono ===');
        \Log::info('Datos recibidos:', $request->all());

        if (!Gate::allows('editar-movimientos')) {
            \Log::warning('Permiso denegado para editar movimientos');
            return back()->with('error', 'No tienes permiso para registrar abonos');
        }

        $validator = Validator::make($request->all(), [
            'id_poliza' => 'required|exists:polizas,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|string|max:50',
            'nota' => 'nullable|string',
            'id_cuenta_fondeadora' => 'required|exists:cuentas,id_cuenta',
            'id_tipo_iva' => 'nullable|exists:tipos_iva,id',
            'monto_base' => 'nullable|numeric|min:0',
            'monto_iva' => 'nullable|numeric|min:0',
            'modo_iva' => 'nullable|in:CON_IVA,SIN_IVA',
        ]);

        if ($validator->fails()) {
            \Log::error('Validación fallida:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            \Log::info('Transacción iniciada');

            $polizaOriginal = Poliza::find($request->id_poliza);
            \Log::info('Póliza original encontrada:', [
                'id' => $polizaOriginal->id ?? 'null',
                'folio' => $polizaOriginal->folio ?? 'null',
                'estatus' => $polizaOriginal->estatus ?? 'null',
                'es_por_pagar' => $polizaOriginal->es_por_pagar ?? 'null',
                'tipo_poliza' => $polizaOriginal->tipo_poliza ?? 'null',
            ]);

            if (!$polizaOriginal) {
                \Log::error('Póliza original no encontrada');
                return back()->with('error', 'Póliza original no encontrada');
            }

            if (in_array($polizaOriginal->estatus, ['LIQUIDADO'])) {
                \Log::warning('Póliza ya liquidada:', ['estatus' => $polizaOriginal->estatus]);
                return back()->with('error', 'No se pueden agregar abonos a una póliza liquidada');
            }

            // 🔥 OBTENER EL MOVIMIENTO ORIGINAL
            $movimientoOriginal = MovimientoPoliza::where('id_poliza', $polizaOriginal->id)->first();
            \Log::info('Movimiento original encontrado:', [
                'id' => $movimientoOriginal->id ?? 'null',
                'monto' => $movimientoOriginal->monto ?? 'null',
                'id_cuenta' => $movimientoOriginal->id_cuenta ?? 'null',
                'id_caja_fondo' => $movimientoOriginal->id_caja_fondo ?? 'null',
            ]);

            if (!$movimientoOriginal) {
                \Log::error('Movimiento original no encontrado');
                return back()->with('error', 'Movimiento original no encontrado');
            }

            // 🔥 CALCULAR SALDOS
            $totalAbonado = AbonoPoliza::where('id_poliza', $polizaOriginal->id)->sum('monto_abonado');
            
            $esTraspaso = $polizaOriginal->tipo_poliza === 'TRASPASO';
            $montoTotal = $esTraspaso 
                ? abs($movimientoOriginal->monto_traspaso ?? $movimientoOriginal->monto)
                : abs($movimientoOriginal->monto);
            
            $saldoPendiente = round($montoTotal - $totalAbonado, 2);
            $montoAbonado = round($request->monto_abonado, 2);

            \Log::info('Saldos calculados:', [
                'totalAbonado_anterior' => $totalAbonado,
                'montoTotal' => $montoTotal,
                'saldoPendiente' => $saldoPendiente,
                'montoAbonado' => $montoAbonado,
                'esTraspaso' => $esTraspaso
            ]);

            if ($montoAbonado > $saldoPendiente) {
                \Log::warning('Monto abonado excede saldo pendiente');
                return back()->with('error', 'El monto abonado no puede ser mayor al saldo pendiente ($' . number_format($saldoPendiente, 2) . ')');
            }

            // 🔥 DETERMINAR EL TIPO DE PÓLIZA ORIGINAL
            $tipoPolizaOriginal = $polizaOriginal->tipo_poliza;
            $esIngresoOriginal = $tipoPolizaOriginal === 'INGRESO';
            
            // 🔥 SIGNO DEL MOVIMIENTO
            // INGRESO = POSITIVO (+)
            // EGRESO = NEGATIVO (-)
            $signo = $esIngresoOriginal ? 1 : -1;

            // 🔥 CREAR NUEVA PÓLIZA PARA EL ABONO
            $fechaActual = now()->toDateString();
            $fechaHoraActual = now()->format('Y-m-d H:i:s');
            
            $nuevaPoliza = Poliza::create([
                'id_empresa' => $polizaOriginal->id_empresa,
                'tipo_poliza' => $esIngresoOriginal ? 'INGRESO' : 'EGRESO',
                'fecha_poliza' => $fechaActual,
                'fecha_vencimiento' => null,
                'categoria' => 'NO_FISCAL',
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => false,
                'referencia' => ($esIngresoOriginal ? 'COB-' : 'ABO-') . $polizaOriginal->folio . '-' . now()->format('YmdHis'),
                'nota' => ($esIngresoOriginal ? 'Cobro' : 'Abono') . ' a póliza ' . $polizaOriginal->folio . ' - ' . ($request->nota ?? ($esIngresoOriginal ? 'Cobro registrado' : 'Abono registrado')) . ' | Fecha: ' . $fechaHoraActual,
                'id_persona' => $polizaOriginal->id_persona,
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'fecha_creacion' => now(),
                'fecha_autorizacion' => null,
                'id_marcador' => $polizaOriginal->id_marcador,
                'fecha_factura' => null,
                'numero_factura' => null,
                'ruta_pdf' => null,
                'nombre_pdf' => null,
                'nombre_xml' => null,
                'uuid_factura' => null,
                'serie_factura' => null,
                'folio_factura' => null,
            ]);

            $nuevaPoliza->refresh();
            \Log::info('Nueva póliza creada:', [
                'id' => $nuevaPoliza->id,
                'folio' => $nuevaPoliza->folio,
                'tipo' => $esIngresoOriginal ? 'INGRESO' : 'EGRESO',
            ]);

            // 🔥 CALCULAR BASE E IVA
            $montoBase = $request->monto_base ?? $montoAbonado;
            $montoIva = $request->monto_iva ?? 0;
            
            $modoIva = $request->modo_iva ?? 'CON_IVA';
            if ($modoIva === 'SIN_IVA') {
                $montoBase = $montoAbonado;
                $montoIva = 0;
            }

            // 🔥 CREAR MOVIMIENTO PARA EL ABONO
            $nuevoMovimiento = MovimientoPoliza::create([
                'id_poliza' => $nuevaPoliza->id,
                'id_cuenta' => $movimientoOriginal->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
                'id_tipo_iva' => $request->id_tipo_iva ?? null,
                'monto' => round($montoAbonado * $signo, 2),
                'monto_base' => round($montoBase * $signo, 2),
                'monto_iva' => round($montoIva * $signo, 2),
                'monto_traspaso' => null,
                'monto_iva_cero' => 0,
                'monto_iva_dieciseis' => 0,
                'iva_dieciseis' => 0,
            ]);

            \Log::info('Movimiento creado:', [
                'monto' => round($montoAbonado * $signo, 2),
                'id_cuenta' => $movimientoOriginal->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
            ]);

            // 🔥 🔥 🔥 ACTUALIZAR SALDOS - SUMA/RESTA DIRECTA 🔥 🔥 🔥
            // ✅ SOLO sumamos o restamos el MONTO DEL ABONO a las cuentas
            // ❌ NO recalculamos todo desde cero
            
            // 1️⃣ ACTUALIZAR LA CUENTA FONDEADORA
            $cuentaFondeadora = Cuenta::find($request->id_cuenta_fondeadora);
            if ($cuentaFondeadora) {
                $saldoAnteriorFondeadora = (float) ($cuentaFondeadora->saldo_inicial ?? 0);
                
                if ($esIngresoOriginal) {
                    // 🔥 INGRESO: El dinero ENTRA a la fondeadora → SUMAR
                    $nuevoSaldoFondeadora = $saldoAnteriorFondeadora + $montoAbonado;
                } else {
                    // 🔥 EGRESO: El dinero SALE de la fondeadora → RESTAR
                    $nuevoSaldoFondeadora = $saldoAnteriorFondeadora - $montoAbonado;
                }
                
                $cuentaFondeadora->saldo_inicial = $nuevoSaldoFondeadora;
                $cuentaFondeadora->save();
                
                \Log::info('Saldo fondeadora actualizado:', [
                    'id_cuenta' => $request->id_cuenta_fondeadora,
                    'nombre' => $cuentaFondeadora->nombre_cuenta,
                    'saldo_anterior' => $saldoAnteriorFondeadora,
                    'monto_abonado' => $montoAbonado,
                    'operacion' => $esIngresoOriginal ? 'SUMA' : 'RESTA',
                    'nuevo_saldo' => $nuevoSaldoFondeadora
                ]);
            }

            // 2️⃣ ACTUALIZAR LA CUENTA DE LA PÓLIZA (si es diferente a la fondeadora)
            if ($movimientoOriginal->id_cuenta && $movimientoOriginal->id_cuenta != $request->id_cuenta_fondeadora) {
                $cuentaPoliza = Cuenta::find($movimientoOriginal->id_cuenta);
                if ($cuentaPoliza) {
                    $saldoAnteriorPoliza = (float) ($cuentaPoliza->saldo_inicial ?? 0);
                    
                    if ($esIngresoOriginal) {
                        // 🔥 INGRESO: El dinero ENTRA a la cuenta de la póliza → SUMAR
                        $nuevoSaldoPoliza = $saldoAnteriorPoliza + $montoAbonado;
                    } else {
                        // 🔥 EGRESO: El dinero SALE de la cuenta de la póliza → RESTAR
                        $nuevoSaldoPoliza = $saldoAnteriorPoliza - $montoAbonado;
                    }
                    
                    $cuentaPoliza->saldo_inicial = $nuevoSaldoPoliza;
                    $cuentaPoliza->save();
                    
                    \Log::info('Saldo cuenta de póliza actualizado:', [
                        'id_cuenta' => $movimientoOriginal->id_cuenta,
                        'nombre' => $cuentaPoliza->nombre_cuenta,
                        'saldo_anterior' => $saldoAnteriorPoliza,
                        'monto_abonado' => $montoAbonado,
                        'operacion' => $esIngresoOriginal ? 'SUMA' : 'RESTA',
                        'nuevo_saldo' => $nuevoSaldoPoliza
                    ]);
                }
            }

            // 🔥 REGISTRAR EL ABONO EN LA PÓLIZA ORIGINAL
            $abono = AbonoPoliza::create([
                'id_poliza' => $polizaOriginal->id,
                'monto_abonado' => $montoAbonado,
                'fecha_abono' => $fechaActual,
                'referencia' => $request->referencia ?? ($esIngresoOriginal ? 'COB-' : 'ABO-') . $nuevaPoliza->folio,
                'metodo_pago' => $request->metodo_pago ?? 'TRANSFERENCIA',
                'nota' => $request->nota . ' | Póliza de ' . ($esIngresoOriginal ? 'cobro' : 'egreso') . ' generada: ' . $nuevaPoliza->folio,
                'id_usuario' => auth()->id(),
                'id_poliza_egreso' => $nuevaPoliza->id,
            ]);

            // 🔥 ACTUALIZAR ESTATUS DE LA PÓLIZA ORIGINAL
            $totalAbonadoActual = AbonoPoliza::where('id_poliza', $polizaOriginal->id)->sum('monto_abonado');

            \Log::info('Total abonado actualizado:', [
                'totalAbonadoActual' => $totalAbonadoActual,
                'montoTotal_requerido' => $montoTotal,
            ]);

            if ($totalAbonadoActual >= $montoTotal) {
                $polizaOriginal->estatus = 'LIQUIDADO';
                $polizaOriginal->fecha_liquidacion = now();
                \Log::info('Póliza marcada como LIQUIDADO');
            } elseif ($totalAbonadoActual > 0) {
                $polizaOriginal->estatus = 'ABONADO';
                $polizaOriginal->fecha_abono = now();
                \Log::info('Póliza marcada como ABONADO');
            }

            $polizaOriginal->save();

            DB::commit();
            \Log::info('=== storeAbono COMPLETADO EXITOSAMENTE ===');

            $saldoNuevo = $saldoPendiente - $montoAbonado;
            $tipoAccion = $esIngresoOriginal ? 'cobro' : 'abono';
            $tipoPolizaTexto = $esIngresoOriginal ? 'cobro' : 'egreso';
            
            $mensaje = $tipoAccion . ' registrado exitosamente. ' .
                    'Se creó la póliza de ' . $tipoPolizaTexto . ': ' . $nuevaPoliza->folio . 
                    ' por $' . number_format($montoAbonado, 2) . 
                    '. Nuevo saldo pendiente: $' . number_format($saldoNuevo, 2);

            if ($totalAbonadoActual >= $montoTotal) {
                $mensaje .= ' ¡Póliza liquidada completamente!';
            }

            return redirect()->route('movimientos.index', ['vista' => 'diferidas'])
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERROR EN storeAbono ===');
            \Log::error('Mensaje de error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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

            if ($abono->id_poliza_egreso) {
                $polizaEgreso = Poliza::find($abono->id_poliza_egreso);
                if ($polizaEgreso) {
                    $movimientoEgreso = MovimientoPoliza::where('id_poliza', $polizaEgreso->id)->first();
                    if ($movimientoEgreso) {
                        $idCuentaFondeadora = $movimientoEgreso->id_caja_fondo;
                        if ($idCuentaFondeadora) {
                            $this->actualizarSaldoCuenta($idCuentaFondeadora);
                        }
                        if ($movimientoEgreso->id_cuenta && $movimientoEgreso->id_cuenta != $idCuentaFondeadora) {
                            $this->actualizarSaldoCuenta($movimientoEgreso->id_cuenta);
                        }
                        $movimientoEgreso->delete();
                    }
                    $polizaEgreso->delete();
                }
            }

            $abono->delete();
            $this->actualizarEstatusPoliza($poliza);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Abono y póliza de egreso eliminados exitosamente'
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

            $empresaId = $poliza->id_empresa;
            
            $nuevaPoliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => 'EGRESO',
                'fecha_poliza' => now()->toDateString(),
                'fecha_vencimiento' => null,
                'categoria' => 'NO_FISCAL',
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => false,
                'referencia' => 'LIQ-' . $poliza->folio . '-' . date('YmdHis'),
                'nota' => 'Liquidación de póliza ' . $poliza->folio . ' - Cuenta fondeadora: ' . $cuentaFondeadora->nombre_cuenta,
                'id_persona' => $poliza->id_persona,
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'fecha_creacion' => now(),
                'fecha_autorizacion' => null,
                'id_marcador' => $poliza->id_marcador,
                'fecha_factura' => null,
                'numero_factura' => null,
                'ruta_pdf' => null,
                'nombre_pdf' => null,
                'nombre_xml' => null,
                'uuid_factura' => null,
                'serie_factura' => null,
                'folio_factura' => null,
            ]);

            $nuevaPoliza->refresh();

            MovimientoPoliza::create([
                'id_poliza' => $nuevaPoliza->id,
                'id_cuenta' => $movimiento->id_cuenta,
                'id_caja_fondo' => $request->id_cuenta_fondeadora,
                'id_tipo_iva' => null,
                'monto' => -$saldoPendiente,
                'monto_base' => -$saldoPendiente,
                'monto_iva' => 0,
                'monto_traspaso' => null,
                'monto_iva_cero' => 0,
                'monto_iva_dieciseis' => 0,
                'iva_dieciseis' => 0,
            ]);

            $cuentaFondeadora->saldo_inicial = $saldoActual - $saldoPendiente;
            $cuentaFondeadora->save();

            $abono = AbonoPoliza::create([
                'id_poliza' => $poliza->id,
                'monto_abonado' => $saldoPendiente,
                'fecha_abono' => now()->toDateString(),
                'referencia' => 'LIQUIDACION AUTOMATICA - ' . ($request->referencia ?? ''),
                'metodo_pago' => 'TRANSFERENCIA',
                'nota' => 'Liquidación total de póliza. Póliza de egreso: ' . $nuevaPoliza->folio,
                'id_usuario' => auth()->id(),
                'id_poliza_egreso' => $nuevaPoliza->id,
            ]);

            $poliza->estatus = 'LIQUIDADO';
            $poliza->fecha_liquidacion = now();
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza liquidada exitosamente. Se creó la póliza de egreso: ' . $nuevaPoliza->folio
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
                'id_usuario' => auth()->id(),
                'id_poliza_egreso' => null,
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
            ->where('empleado', 1)
            ->orderBy('Nombre')
            ->orderBy('Paterno')
            ->orderBy('Materno')
            ->get()
            ->map(function($persona) {
                return [
                    'id_persona' => $persona->id_persona,
                    'nombre_completo' => $persona->nombre_completo,
                    'rfc' => $persona->rfc,
                    'empleado' => $persona->empleado,
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

        $cuentasNomina = Cuenta::where('id_empresa', $empresaId)
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
                ->with('info', 'No hay empleados registrados. Primero debes registrar empleados (marcar campo "empleado" como 1).');
        }

        return Inertia::render('Movimientos/NominaCreate', [
            'empresa_id' => (int) $empresaId,
            'empleados' => $empleados,
            'cuentas_fondeadoras' => $cuentasFondeadoras,
            'cuentas_nomina' => $cuentasNomina,
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
                $persona = Persona::find($empleadoData['id_persona']);
                if (!$persona || !$persona->empleado) {
                    throw new \Exception('La persona ' . ($persona ? $persona->nombre_completo : 'desconocida') . ' no es un empleado');
                }

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
                    'ruta_pdf' => null,
                    'nombre_pdf' => null,
                    'nombre_xml' => null,
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
                    'monto_iva_cero' => 0,
                    'monto_iva_dieciseis' => 0,
                    'iva_dieciseis' => 0,
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
    // ✅ REVISAR PÓLIZA
    // ============================================
    public function revisarPoliza(Request $request, $id){
        if (!Gate::allows('editar-movimientos')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para revisar pólizas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $poliza = Poliza::findOrFail($id);
            
            if ($poliza->estatus !== 'CAPTURADO' && $poliza->estatus !== 'PENDIENTE') {
                return response()->json([
                    'success' => false,
                    'message' => 'La póliza debe estar en estado CAPTURADO o PENDIENTE para ser revisada'
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
    // ✅ AUTORIZAR PÓLIZA
    // ============================================
    public function autorizarPoliza(Request $request, $id){
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
    // ❌ RECHAZAR PÓLIZA
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

            $nuevoEstatus = $poliza->es_por_pagar ? 'PENDIENTE' : 'CAPTURADO';
            
            $poliza->estatus = $nuevoEstatus;
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
                'message' => 'Póliza rechazada. Regresa a estado ' . $this->getEstatusTexto($nuevoEstatus),
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
    // 🔄 REVERTIR REVISIÓN
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

            $nuevoEstatus = $poliza->es_por_pagar ? 'PENDIENTE' : 'CAPTURADO';
            
            $poliza->estatus = $nuevoEstatus;
            $poliza->id_usuario_revisor = null;
            $poliza->fecha_revision = null;
            $poliza->comentario_revision = null;
            $poliza->motivo_rechazo = $request->motivo;
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revisión revertida. La póliza regresa a estado ' . $this->getEstatusTexto($nuevoEstatus),
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
    // 📄 OBTENER DOCUMENTO FISCAL
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
            ->where('empleado', 1)
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
                    'empleado' => $persona->empleado,
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
                'cuenta' => function($q) {
                    $q->where('en_uso', true);
                },
                'cuentaFondeadora' => function($q) {
                    $q->where('en_uso', true);
                },
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
                        $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                        $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->cuentaFondeadora->nombre_cuenta : 'N/A',
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
                'cuenta' => function($q) {
                    $q->where('en_uso', true);
                },
                'cuentaFondeadora' => function($q) {
                    $q->where('en_uso', true);
                },
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
                        'cuenta' => $movimiento->cuenta && $movimiento->cuenta->en_uso ? $movimiento->cuenta->nombre_cuenta : 'N/A',
                        'cuenta_fondeadora' => $movimiento->cuentaFondeadora && $movimiento->cuentaFondeadora->en_uso ? $movimiento->cuentaFondeadora->nombre_cuenta : 'N/A',
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
                    'empleado' => $persona->empleado,
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
        $empresaId = session('empresa_movimientos');
        
        if (!$idPersona) {
            return response()->json(['error' => 'ID de persona requerido'], 400);
        }

        if (!$empresaId) {
            return response()->json(['error' => 'No hay empresa seleccionada'], 400);
        }

        $persona = Persona::find($idPersona);
        if (!$persona) {
            return response()->json(['error' => 'Persona no encontrada'], 404);
        }

        // 🔥 IMPORTANTE: Filtrar por empresa
        $ultimaPoliza = Poliza::where('id_persona', $idPersona)
            ->where('id_empresa', $empresaId)  // 🔥 SOLO DE LA EMPRESA ACTUAL
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ultimaPoliza) {
            return response()->json([
                'existe' => false,
                'persona_nombre' => $persona->nombre_completo,
                'mensaje' => 'No hay pólizas registradas para esta persona en esta empresa'
            ]);
        }

        $movimiento = MovimientoPoliza::where('id_poliza', $ultimaPoliza->id)->first();

        if (!$movimiento) {
            return response()->json([
                'existe' => false,
                'persona_nombre' => $persona->nombre_completo,
                'mensaje' => 'La póliza no tiene movimiento asociado'
            ]);
        }

        // 🔥 VERIFICAR QUE LA CUENTA FONDEADORA EXISTA Y ESTÉ ACTIVA EN LA EMPRESA ACTUAL
        $cuentaFondeadoraId = $movimiento->id_caja_fondo;
        $cuentaFondeadoraValida = false;
        
        if ($cuentaFondeadoraId) {
            $cuentaFondeadoraValida = Cuenta::where('id_cuenta', $cuentaFondeadoraId)
                ->where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->exists();
        }

        // 🔥 VERIFICAR QUE LA CUENTA EXISTA Y ESTÉ ACTIVA EN LA EMPRESA ACTUAL
        $cuentaId = $movimiento->id_cuenta;
        $cuentaValida = false;
        
        if ($cuentaId) {
            $cuentaValida = Cuenta::where('id_cuenta', $cuentaId)
                ->where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->exists();
        }

        return response()->json([
            'existe' => true,
            'persona_nombre' => $persona->nombre_completo,
            'id_cuenta' => $cuentaValida ? $movimiento->id_cuenta : null,
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
            // 🔥 DEVOLVER SOLO SI LA CUENTA FONDEADORA EXISTE EN LA EMPRESA ACTUAL
            'id_cuenta_fondeadora' => $cuentaFondeadoraValida ? $movimiento->id_caja_fondo : null,
            'cuenta_fondeadora_valida' => $cuentaFondeadoraValida,
            'cuenta_valida' => $cuentaValida,
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
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
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
    // 🔄 CERRAR PÓLIZA
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
    // 📄 REABRIR PÓLIZA
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

            $nuevoEstatus = $poliza->es_por_pagar ? 'PENDIENTE' : 'CAPTURADO';
            
            $poliza->estatus = $nuevoEstatus;
            $poliza->fecha_cierre = null;
            $poliza->motivo_cierre = null;
            $poliza->id_usuario_cierre = null;
            $poliza->motivo_rechazo = $request->motivo ?? 'Póliza reabierta';
            $poliza->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Póliza reabierta exitosamente. Regresa a estado ' . $this->getEstatusTexto($nuevoEstatus),
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
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
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

        $historial[] = [
            'fecha' => $poliza->created_at?->format('d/m/Y H:i'),
            'evento' => 'CREACIÓN',
            'descripcion' => 'Póliza creada por ' . ($poliza->usuarioCreador?->nombre_completo ?? 'Sistema'),
            'usuario' => $poliza->usuarioCreador?->nombre_completo ?? 'Sistema',
            'estatus' => $poliza->es_por_pagar ? 'PENDIENTE' : 'CAPTURADO'
        ];

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

        usort($historial, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return response()->json([
            'success' => true,
            'data' => $historial
        ]);
    }

    // ============================================
    // 💰 STORE LIQUIDACIÓN - LIQUIDAR PÓLIZA DIFERIDA COMPLETAMENTE
    // ============================================
    public function storeLiquidacion(Request $request)
    {
        \Log::info('=== INICIO storeLiquidacion ===');
        \Log::info('Datos recibidos:', $request->all());

        if (!Gate::allows('editar-movimientos')) {
            \Log::warning('Permiso denegado para liquidar pólizas');
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para liquidar pólizas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_movimiento' => 'required|exists:movimientos_poliza,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Validación fallida:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();
            \Log::info('Transacción iniciada');

            $movimientoOriginal = MovimientoPoliza::with(['poliza', 'cuenta', 'cuentaFondeadora'])
                ->find($request->id_movimiento);
            
            if (!$movimientoOriginal) {
                \Log::error('Movimiento original no encontrado');
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento original no encontrado'
                ], 404);
            }

            $polizaOriginal = $movimientoOriginal->poliza;
            
            if (!$polizaOriginal) {
                \Log::error('Póliza original no encontrada');
                return response()->json([
                    'success' => false,
                    'message' => 'Póliza original no encontrada'
                ], 404);
            }

            if (in_array($polizaOriginal->estatus, ['LIQUIDADO'])) {
                \Log::warning('Póliza ya liquidada:', ['estatus' => $polizaOriginal->estatus]);
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está liquidada'
                ], 400);
            }

            $totalAbonado = AbonoPoliza::where('id_poliza', $polizaOriginal->id)->sum('monto_abonado');
            
            $esTraspaso = $polizaOriginal->tipo_poliza === 'TRASPASO';
            $montoTotal = $esTraspaso 
                ? abs($movimientoOriginal->monto_traspaso ?? $movimientoOriginal->monto)
                : abs($movimientoOriginal->monto);
            
            $saldoPendiente = round($montoTotal - $totalAbonado, 2);
            
            if ($saldoPendiente <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta póliza ya está completamente pagada/cobrada'
                ], 400);
            }

            $esIngreso = $movimientoOriginal->monto > 0;
            $esEgreso = $movimientoOriginal->monto < 0;

            $cuentaFondeadoraId = null;
            $cuentaFondeadora = null;

            if ($esEgreso) {
                $cuentaFondeadoraId = $movimientoOriginal->id_caja_fondo;
                
                if (!$cuentaFondeadoraId) {
                    $cuentaFondeadoraId = $this->obtenerCuentaFondeadoraDefecto($polizaOriginal->id_empresa);
                }

                if (!$cuentaFondeadoraId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontró una cuenta fondeadora para esta póliza de egreso.'
                    ], 400);
                }

                $cuentaFondeadora = Cuenta::find($cuentaFondeadoraId);
                if (!$cuentaFondeadora) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cuenta fondeadora no encontrada'
                    ], 404);
                }

                $saldoActual = (float) ($cuentaFondeadora->saldo_inicial ?? 0);
                if ($saldoActual < $saldoPendiente) {
                    return response()->json([
                        'success' => false,
                        'message' => "Saldo insuficiente en la cuenta fondeadora. Disponible: $" . number_format($saldoActual, 2) . ", Necesario: $" . number_format($saldoPendiente, 2)
                    ], 400);
                }
            }

            $empresaId = $polizaOriginal->id_empresa;
            $fechaLiquidacion = now()->toDateString();
            
            $tipoPolizaLiquidacion = $esIngreso ? 'INGRESO' : 'EGRESO';
            
            $nuevaPoliza = Poliza::create([
                'id_empresa' => $empresaId,
                'tipo_poliza' => $tipoPolizaLiquidacion,
                'fecha_poliza' => $fechaLiquidacion,
                'fecha_vencimiento' => null,
                'categoria' => 'NO_FISCAL',
                'estatus' => 'CAPTURADO',
                'es_por_pagar' => false,
                'referencia' => ($esIngreso ? 'COB-' : 'PAG-') . $polizaOriginal->folio,
                'nota' => ($esIngreso ? 'COBRO' : 'PAGO') . ' TOTAL de póliza ' . $polizaOriginal->folio . ' - Liquidación completa',
                'id_persona' => $polizaOriginal->id_persona,
                'id_usuario_creador' => auth()->id(),
                'id_usuario_autorizador' => null,
                'fecha_creacion' => now(),
                'fecha_autorizacion' => null,
                'id_marcador' => $polizaOriginal->id_marcador,
                'fecha_factura' => null,
                'numero_factura' => null,
                'ruta_pdf' => null,
                'nombre_pdf' => null,
                'nombre_xml' => null,
                'uuid_factura' => null,
                'serie_factura' => null,
                'folio_factura' => null,
            ]);

            $nuevaPoliza->refresh();

            $montoLiquidacion = $esIngreso ? $saldoPendiente : -$saldoPendiente;
            
            MovimientoPoliza::create([
                'id_poliza' => $nuevaPoliza->id,
                'id_cuenta' => $movimientoOriginal->id_cuenta,
                'id_caja_fondo' => $esEgreso ? $cuentaFondeadoraId : null,
                'id_tipo_iva' => null,
                'monto' => $montoLiquidacion,
                'monto_base' => $montoLiquidacion,
                'monto_iva' => 0,
                'monto_traspaso' => null,
                'monto_iva_cero' => 0,
                'monto_iva_dieciseis' => 0,
                'iva_dieciseis' => 0,
            ]);

            if ($esEgreso && $cuentaFondeadoraId) {
                $this->actualizarSaldoCuenta($cuentaFondeadoraId);
            }

            if ($esIngreso) {
                $this->actualizarSaldoCuenta($movimientoOriginal->id_cuenta);
            }

            if ($esEgreso && $movimientoOriginal->id_cuenta && $movimientoOriginal->id_cuenta != $cuentaFondeadoraId) {
                $this->actualizarSaldoCuenta($movimientoOriginal->id_cuenta);
            }

            AbonoPoliza::create([
                'id_poliza' => $polizaOriginal->id,
                'monto_abonado' => $saldoPendiente,
                'fecha_abono' => $fechaLiquidacion,
                'referencia' => ($esIngreso ? 'COBRO-' : 'PAGO-') . $nuevaPoliza->folio,
                'metodo_pago' => 'TRANSFERENCIA',
                'nota' => ($esIngreso ? 'COBRO' : 'PAGO') . ' TOTAL - Póliza liquidada completamente | Póliza de ' . ($esIngreso ? 'cobro' : 'pago') . ': ' . $nuevaPoliza->folio,
                'id_usuario' => auth()->id(),
                'id_poliza_egreso' => $nuevaPoliza->id,
            ]);

            $polizaOriginal->estatus = 'LIQUIDADO';
            $polizaOriginal->fecha_liquidacion = now();
            $polizaOriginal->save();

            DB::commit();

            $tipoAccion = $esIngreso ? 'cobrada' : 'liquidada';
            $mensaje = '✅ Póliza ' . $tipoAccion . ' exitosamente. ' .
                    'Se creó la póliza de ' . ($esIngreso ? 'cobro' : 'pago') . ': ' . $nuevaPoliza->folio . 
                    ' por $' . number_format($saldoPendiente, 2) . 
                    '. La póliza ' . $polizaOriginal->folio . ' ha sido ' . $tipoAccion . ' completamente.';

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'tipo' => $esIngreso ? 'INGRESO' : 'EGRESO',
                    'poliza_liquidacion' => $nuevaPoliza->folio,
                    'monto_liquidado' => $saldoPendiente,
                    'cuenta_fondeadora' => $esEgreso ? ($cuentaFondeadora->nombre_cuenta ?? 'N/A') : 'N/A (Ingreso)',
                    'poliza_original' => $polizaOriginal->folio
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en storeLiquidacion:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al liquidar la póliza: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la cuenta fondeadora por defecto de la empresa
     */
    private function obtenerCuentaFondeadoraDefecto($idEmpresa)
    {
        $cuenta = Cuenta::where('id_empresa', $idEmpresa)
            ->where('en_uso', true)
            ->where('tipo_cuenta', 'FONDEADORA')
            ->first();

        if (!$cuenta) {
            $cuenta = Cuenta::where('id_empresa', $idEmpresa)
                ->where('en_uso', true)
                ->where('tipo_cuenta', 'CAJA')
                ->first();
        }

        if (!$cuenta) {
            $cuenta = Cuenta::where('id_empresa', $idEmpresa)
                ->where('en_uso', true)
                ->first();
        }

        return $cuenta ? $cuenta->id_cuenta : null;
    }

        // ============================================
    // 🖨️ IMPRIMIR PÓLIZA (VERSIÓN PARA Ctrl+P)
    // ============================================
    public function imprimirPoliza($id)
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
            'cuenta' => function($q) {
                $q->where('en_uso', true);
            },
            'cuentaFondeadora' => function($q) {
                $q->where('en_uso', true);
            },
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

        $empresa = $movimiento->poliza->empresa;

        $data = [
            'movimiento' => $movimiento,
            'esTraspaso' => $esTraspaso,
            'montoMostrar' => $montoMostrar,
            'totalAbonado' => $totalAbonado,
            'saldoPendiente' => $saldoPendiente,
            'empresa' => $empresa,
            'abonos' => $abonos,
            'fecha_exportacion' => now()->format('d/m/Y H:i:s')
        ];

        return view('exports.poliza_print', $data);
    }


// ============================================
// 📎 SUBIR ARCHIVO ADJUNTO A PÓLIZA
// ============================================
public function subirArchivo(Request $request, $idPoliza)
{
    if (!Gate::allows('editar-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para subir archivos'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'archivo' => 'required|file|max:10240', // 10MB max
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $poliza = Poliza::find($idPoliza);
        if (!$poliza) {
            throw new \Exception('Póliza no encontrada');
        }

        $archivo = $request->file('archivo');
        $nombreOriginal = $archivo->getClientOriginalName();
        $mimeType = $archivo->getMimeType();
        $tamano = $archivo->getSize();
        
        // Determinar tipo de archivo
        $tipoArchivo = 'other';
        if (str_contains($mimeType, 'pdf') || $archivo->getClientOriginalExtension() === 'pdf') {
            $tipoArchivo = 'pdf';
        } elseif (str_contains($mimeType, 'image')) {
            $tipoArchivo = 'image';
        }

        // Generar nombre único
        $nombreGuardado = 'poliza_' . $idPoliza . '_' . time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
        
        // Guardar archivo
        $ruta = $archivo->storeAs('poliza_archivos', $nombreGuardado, 'public');

        // Crear registro en base de datos
        $archivoDB = PolizaArchivo::create([
            'id_poliza' => $idPoliza,
            'nombre_original' => $nombreOriginal,
            'nombre_guardado' => $nombreGuardado,
            'ruta' => $ruta,
            'tipo_archivo' => $tipoArchivo,
            'mime_type' => $mimeType,
            'tamano' => $tamano,
            'id_usuario_subio' => auth()->id(),
            'descripcion' => $request->descripcion ?? null
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Archivo subido exitosamente',
            'data' => [
                'id' => $archivoDB->id,
                'nombre_original' => $archivoDB->nombre_original,
                'url' => $archivoDB->url
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al subir archivo:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error al subir el archivo: ' . $e->getMessage()
        ], 500);
    }
}

// ============================================
// 📎 ELIMINAR ARCHIVO ADJUNTO
// ============================================
public function eliminarArchivo($idArchivo)
{
    if (!Gate::allows('editar-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para eliminar archivos'
        ], 403);
    }

    try {
        DB::beginTransaction();

        $archivo = PolizaArchivo::find($idArchivo);
        if (!$archivo) {
            throw new \Exception('Archivo no encontrado');
        }

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($archivo->ruta)) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        // Eliminar registro
        $archivo->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Archivo eliminado exitosamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al eliminar archivo:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
        ], 500);
    }
}

// ============================================
// 📎 VER ARCHIVO ADJUNTO
// ============================================
public function verArchivo($idArchivo)
{
    if (!Gate::allows('ver-movimientos')) {
        abort(403, 'No tienes permiso para ver archivos');
    }

    $archivo = PolizaArchivo::find($idArchivo);
    if (!$archivo) {
        abort(404, 'Archivo no encontrado');
    }

    if (!Storage::disk('public')->exists($archivo->ruta)) {
        abort(404, 'Archivo no encontrado en el servidor');
    }

    return Storage::disk('public')->response($archivo->ruta, $archivo->nombre_original);
}


// ============================================
// 📌 STORE MARCADOR - CREAR NUEVO MARCADOR
// ============================================
public function storeMarcador(Request $request)
{
    if (!Gate::allows('crear-movimientos')) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para crear marcadores'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'nombre_marcador' => 'required|string|max:100|unique:marcadores,nombre_marcador',
        'descripcion' => 'nullable|string|max:255',
        'activo' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $marcador = Marcador::create([
            'nombre_marcador' => $request->nombre_marcador,
            'descripcion' => $request->descripcion,
            'activo' => $request->input('activo', true)
        ]);

        DB::commit();

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
        DB::rollBack();
        \Log::error('Error al crear marcador:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el marcador: ' . $e->getMessage()
        ], 500);
    }
}

} // FIN DEL CONTROLADOR