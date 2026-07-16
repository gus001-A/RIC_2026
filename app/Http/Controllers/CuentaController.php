<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log; 


class CuentaController extends Controller
{
   /**
 * Display a listing of the resource.
 */
public function index(Request $request)
{
    if (!Gate::allows('ver-cuentas')) {
        return redirect()->route('dashboard')
            ->with('error', 'No tienes permiso para ver cuentas');
    }

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
        $empresas = Empresa::whereIn('id', $empresaIds)
            ->where('activo', true)
            ->get();
    } else {
        $empresas = collect();
    }
    
    // 🔥 INTENTAR OBTENER EMPRESA DE: 1) Request, 2) Sesión, 3) LocalStorage (vía request)
    $empresaId = $request->input('empresa_id');
    
    // Si no viene en la request, intentar desde la sesión
    if (!$empresaId) {
        $empresaId = session('empresa_seleccionada');
    }
    
    // Si aún no hay, usar la primera empresa disponible
    if (!$empresaId && $empresas->count() > 0) {
        $empresaId = $empresas->first()->id;
    }
    
    // Validar que el usuario tenga acceso a la empresa
    if ($empresaId) {
        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresaId)
            ->where('id_usuario', $userId)
            ->exists();
            
        if (!$tieneAcceso) {
            $empresaId = null;
            // Si no tiene acceso, intentar usar la primera disponible
            if ($empresas->count() > 0) {
                $empresaId = $empresas->first()->id;
            }
        }
    }
    
    // 🔥 GUARDAR EN SESIÓN para persistir entre requests
    if ($empresaId) {
        session(['empresa_seleccionada' => $empresaId]);
    }

    $cuentasData = [
        'data' => [],
        'from' => 0,
        'to' => 0,
        'total' => 0,
        'links' => []
    ];
    
    $stats = null;
    $filtros = $request->only(['codigo_cuenta', 'nombre_cuenta', 'nivel', 'Naturaleza', 'tipo_cuenta']);

    if ($empresaId) {
        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresaId)
            ->where('id_usuario', $userId)
            ->exists();
            
        if ($tieneAcceso) {
            $stats = [
                'total' => Cuenta::where('id_empresa', $empresaId)
                    ->where('en_uso', true)
                    ->count(),
                'deudoras' => Cuenta::where('id_empresa', $empresaId)
                    ->where('en_uso', true)
                    ->where('Naturaleza', 'DEUDORA')
                    ->count(),
                'acreedoras' => Cuenta::where('id_empresa', $empresaId)
                    ->where('en_uso', true)
                    ->where('Naturaleza', 'ACREEDORA')
                    ->count(),
            ];

            $query = Cuenta::where('id_empresa', $empresaId)
                ->where('en_uso', true)
                ->orderBy('id_cuenta', 'asc');

            if ($request->filled('codigo_cuenta')) {
                $query->where('codigo_cuenta', 'LIKE', '%' . $request->codigo_cuenta . '%');
            }
            if ($request->filled('nombre_cuenta')) {
                $query->where('nombre_cuenta', 'LIKE', '%' . $request->nombre_cuenta . '%');
            }
            if ($request->filled('nivel')) {
                $query->where('nivel', $request->nivel);
            }
            if ($request->filled('Naturaleza')) {
                $query->where('Naturaleza', $request->Naturaleza);
            }
            if ($request->filled('tipo_cuenta')) {
                $query->where('tipo_cuenta', $request->tipo_cuenta);
            }

            $cuentas = $query->paginate(100);

            $cuentasData = [
                'data' => $cuentas->items(),
                'from' => $cuentas->firstItem(),
                'to' => $cuentas->lastItem(),
                'total' => $cuentas->total(),
                'links' => $cuentas->linkCollection()->toArray(),
            ];

            $startIndex = $cuentas->firstItem() ?? 0;
            $cuentasData['data'] = collect($cuentasData['data'])->map(function($cuenta, $index) use ($startIndex) {
                $jerarquia = $cuenta->nivel_jerarquico;
                
                $nombreMadre = 'SIN MADRE';
                if ($cuenta->id_cuenta_madre) {
                    $madre = Cuenta::find($cuenta->id_cuenta_madre);
                    if ($madre) {
                        $nombreMadre = $madre->nombre_cuenta;
                    }
                }
                
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'row_number' => $startIndex + $index,
                    'codigo_cuenta' => $cuenta->codigo_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'nivel' => $cuenta->nivel,
                    'nivel_texto' => $cuenta->nivel_texto,
                    'nivel_jerarquico' => $jerarquia,
                    'indice' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta,
                    'cuenta_madre' => $nombreMadre,
                    'tipo_cuenta' => $cuenta->tipo_cuenta,
                    'Naturaleza' => $cuenta->Naturaleza,
                    'en_uso' => $cuenta->en_uso,
                    'descripcion' => $cuenta->descripcion,
                    'es_cuenta_resultados' => $cuenta->es_cuenta_resultados,
                    'saldo_inicial' => $cuenta->saldo_inicial,
                    'indice_c' => $cuenta->indice_c,
                    'fondeo_c' => $cuenta->fondeo_c ?? 0,
                    'cuenta_resultados' => $cuenta->cuenta_resultados ?? 0,
                ];
            })->toArray();
        }
    }

    $flash = [];
    $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
    foreach ($flashTypes as $type) {
        if (session()->has($type)) {
            $flash[$type] = session($type);
        }
    }

    return Inertia::render('Cuentas/Index', [
        'empresas' => $empresas,
        'empresa_seleccionada' => $empresaId,
        'cuentas' => $cuentasData,
        'stats' => $stats,
        'filtros' => $filtros,
        'datatable_url' => route('cuentas.datatable'),
        'flash' => $flash,
    ]);
}

    private function getNivelTexto($nivel)
    {
        $niveles = ['', 'Primer Nivel', 'Segundo Nivel', 'Tercer Nivel', 'Cuarto Nivel', 'Quinto Nivel'];
        return $niveles[$nivel] ?? "Nivel {$nivel}";
    }

    public function getCuentas(Request $request)
    {
        if (!Gate::allows('ver-cuentas')) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No tienes permiso para ver cuentas'
            ], 403);
        }

        $empresa_id = $request->input('empresa_id');
        $user = auth()->user();
        
        if (!$user || !$empresa_id) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
        
        $userId = $user->id_usuario;

        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresa_id)
            ->where('id_usuario', $userId)
            ->exists();
            
        if (!$tieneAcceso) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $query = Cuenta::where('id_empresa', $empresa_id)
            ->where('en_uso', true);

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo_cuenta', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_cuenta', 'LIKE', "%{$search}%")
                  ->orWhere('nivel', 'LIKE', "%{$search}%")
                  ->orWhere('Naturaleza', 'LIKE', "%{$search}%")
                  ->orWhere('tipo_cuenta', 'LIKE', "%{$search}%")
                  ->orWhere('indice_c', 'LIKE', "%{$search}%");
            });
        }

        $columns = ['codigo_cuenta', 'nombre_cuenta', 'nivel', 'Naturaleza', 'tipo_cuenta', 'indice_c'];
        for ($i = 0; $i < count($columns); $i++) {
            $colSearch = $request->input("columns.{$i}.search.value");
            if ($colSearch) {
                $query->where($columns[$i], 'LIKE', "%{$colSearch}%");
            }
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        if ($orderColumnIndex !== null && isset($columns[$orderColumnIndex])) {
            $query->orderBy($columns[$orderColumnIndex], $orderDirection);
        } else {
            $query->orderBy('nivel')->orderBy('codigo_cuenta');
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 100);
        $totalRecords = $query->count();
        $query->skip($start)->take($length);

        $cuentas = $query->get();

        $data = [];
        $counter = $start + 1;
        foreach ($cuentas as $cuenta) {
            $jerarquia = $cuenta->nivel_jerarquico;
            
            $nombreMadre = 'SIN MADRE';
            if ($cuenta->id_cuenta_madre) {
                $madre = Cuenta::find($cuenta->id_cuenta_madre);
                if ($madre) {
                    $nombreMadre = $madre->nombre_cuenta;
                }
            }
            
            $data[] = [
                'id_cuenta' => $cuenta->id_cuenta,
                'row_number' => $counter++,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'nivel' => $cuenta->nivel,
                'nivel_texto' => $cuenta->nivel_texto,
                'nivel_jerarquico' => $jerarquia,
                'indice' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta,
                'cuenta_madre' => $nombreMadre,
                'tipo_cuenta' => $cuenta->tipo_cuenta,
                'Naturaleza' => $cuenta->Naturaleza,
                'en_uso' => $cuenta->en_uso,
                'indice_c' => $cuenta->indice_c,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    public function getCuentasMadre(Request $request)
    {
        if (!Gate::allows('ver-cuentas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver cuentas',
                'data' => []
            ], 403);
        }

        $empresaId = $request->input('empresa_id');
        $nivel = $request->input('nivel');
        
        if (!$empresaId || !$nivel) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parametros: empresa_id y nivel',
                'data' => []
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
                'data' => []
            ], 401);
        }

        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresaId)
            ->where('id_usuario', $user->id_usuario)
            ->exists();
            
        if (!$tieneAcceso) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene acceso a esta empresa',
                'data' => []
            ], 403);
        }

        if ($nivel == 1) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Nivel 1 no requiere cuenta madre',
                'total' => 0,
                'nivel_madre' => null
            ]);
        }

        $nivelMadre = $nivel - 1;
        
        $query = Cuenta::where('id_empresa', $empresaId)
            ->where('nivel', $nivelMadre)
            ->where('en_uso', true)
            ->orderBy('codigo_cuenta');

        $cuentas = $query->get();

        if ($cuentas->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No hay cuentas de nivel ' . $nivelMadre,
                'total' => 0,
                'nivel_madre' => $nivelMadre
            ]);
        }

        $data = $cuentas->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'nivel' => $cuenta->nivel,
                'display' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta . ' (Nivel ' . $cuenta->nivel . ')'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
            'nivel_madre' => $nivelMadre,
            'message' => 'Se encontraron ' . $data->count() . ' cuentas de nivel ' . $nivelMadre
        ]);
    }

    public function getCuentasResultados(Request $request)
    {
        if (!Gate::allows('ver-cuentas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver cuentas',
                'data' => []
            ], 403);
        }

        $empresaId = $request->input('empresa_id');
        
        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere empresa_id',
                'data' => []
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
                'data' => []
            ], 401);
        }

        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresaId)
            ->where('id_usuario', $user->id_usuario)
            ->exists();
            
        if (!$tieneAcceso) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene acceso a esta empresa',
                'data' => []
            ], 403);
        }

        $query = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', true)
            ->where('es_cuenta_resultados', true)
            ->orderBy('codigo_cuenta');

        $cuentas = $query->get();

        $data = $cuentas->map(function($cuenta) {
            return [
                'id_cuenta' => $cuenta->id_cuenta,
                'codigo_cuenta' => $cuenta->codigo_cuenta,
                'nombre_cuenta' => $cuenta->nombre_cuenta,
                'nivel' => $cuenta->nivel,
                'display' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta . ' (Nivel ' . $cuenta->nivel . ')'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
            'message' => 'Se encontraron ' . $data->count() . ' cuentas de resultados'
        ]);
    }

    /**
     * Verificar si un código de cuenta ya existe
     */
    public function verificarCodigo(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $codigo = $request->input('codigo');
        
        if (!$empresaId || !$codigo) {
            return response()->json(['exists' => false]);
        }
        
        $exists = Cuenta::where('id_empresa', $empresaId)
            ->where('codigo_cuenta', $codigo)
            ->exists();
            
        return response()->json(['exists' => $exists]);
    }

    public function create()
    {
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para crear cuentas');
        }

        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $userId = $user->id_usuario;
        
        $empresaIds = DB::table('empresas_usuarios')
            ->where('id_usuario', $userId)
            ->pluck('id_empresa')
            ->toArray();
        
        $empresas = Empresa::whereIn('id', $empresaIds)
            ->where('activo', true)
            ->get();
        
        $flash = [];
        $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
        foreach ($flashTypes as $type) {
            if (session()->has($type)) {
                $flash[$type] = session($type);
            }
        }
        
        return Inertia::render('Cuentas/Create', [
            'empresas' => $empresas,
            'flash' => $flash,
        ]);
    }
    public function store(Request $request)
    {
        // ✅ Verificar permiso para crear cuentas
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para crear cuentas');
        }

        try {
            // 🔥 PRIMERO: Normalizar los datos antes de la validación
            $data = $request->all();
            
            // 🔥 Convertir cuenta_resultados a null si es vacío o array
            if (isset($data['cuenta_resultados'])) {
                if (is_array($data['cuenta_resultados']) && empty($data['cuenta_resultados'])) {
                    $data['cuenta_resultados'] = null;
                } elseif (is_string($data['cuenta_resultados']) && trim($data['cuenta_resultados']) === '') {
                    $data['cuenta_resultados'] = null;
                }
            }
            
            // 🔥 Actualizar el request con los datos normalizados
            $request->merge($data);

            $validated = $request->validate([
                'id_empresa' => 'required|exists:empresas,id',
                'codigo_cuenta' => [
                    'required',
                    'string',
                    'max:50',
                    \Illuminate\Validation\Rule::unique('cuentas')->where(function ($query) use ($request) {
                        return $query->where('id_empresa', $request->id_empresa);
                    }),
                ],
                'nombre_cuenta' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'Naturaleza' => 'required|in:DEUDORA,ACREEDORA',
                'id_cuenta_madre' => 'nullable|exists:cuentas,id_cuenta',
                'en_uso' => 'nullable|boolean',
                'es_cuenta_resultados' => 'nullable|boolean',
                'fondeo_c' => 'nullable|boolean',
                'saldo_inicial' => 'nullable|numeric',
                'nivel' => 'required|integer|min:0|max:5',
                'cuenta_resultados' => 'nullable|exists:cuentas,id_cuenta',
            ]);

            // ✅ Verificar que el usuario tiene acceso a la empresa
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $validated['id_empresa'])
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->back()
                    ->with('error', 'No tienes acceso a esta empresa')
                    ->withInput();
            }

            // ✅ Valores por defecto
            $validated['saldo_inicial'] = $validated['saldo_inicial'] ?? 0;
            $validated['en_uso'] = $validated['en_uso'] ?? true;
            $validated['es_cuenta_resultados'] = $validated['es_cuenta_resultados'] ?? false;
            $validated['fondeo_c'] = $validated['fondeo_c'] ?? false;

            // 🔥 LÓGICA CORREGIDA:
            if ($validated['es_cuenta_resultados'] || $validated['fondeo_c']) {
                $validated['cuenta_resultados'] = null;
            } else {
                // 🔥 Asegurar que cuenta_resultados sea un valor válido
                $cuentaResultadosValue = $validated['cuenta_resultados'] ?? null;
                
                if (is_array($cuentaResultadosValue) && empty($cuentaResultadosValue)) {
                    $cuentaResultadosValue = null;
                } elseif (is_string($cuentaResultadosValue) && trim($cuentaResultadosValue) === '') {
                    $cuentaResultadosValue = null;
                }
                
                $validated['cuenta_resultados'] = $cuentaResultadosValue;
                
                if (empty($validated['cuenta_resultados'])) {
                    return redirect()->back()
                        ->with('error', 'Debes seleccionar una cuenta de resultados a la que pertenece esta cuenta')
                        ->withInput();
                }
                
                $cuentaResultados = Cuenta::find($validated['cuenta_resultados']);
                if (!$cuentaResultados || !$cuentaResultados->es_cuenta_resultados) {
                    return redirect()->back()
                        ->with('error', 'La cuenta seleccionada no es una cuenta de resultados válida')
                        ->withInput();
                }
            }

            // ✅ Si es nivel 1, no tiene cuenta madre
            if ($validated['nivel'] == 1) {
                $validated['id_cuenta_madre'] = null;
            }

            // ✅ Verificar cuenta madre
            if ($validated['id_cuenta_madre']) {
                $madre = Cuenta::find($validated['id_cuenta_madre']);
                if (!$madre) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre seleccionada no existe.')
                        ->withInput();
                }
                if ($madre->nivel != ($validated['nivel'] - 1)) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre debe ser de nivel ' . ($validated['nivel'] - 1))
                        ->withInput();
                }
                if ($madre->id_empresa != $validated['id_empresa']) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre no pertenece a la misma empresa')
                        ->withInput();
                }
            }

            // ✅ Buscar cuenta madre automática si no se seleccionó
            if ($validated['nivel'] > 1 && !$validated['id_cuenta_madre']) {
                $madreAuto = Cuenta::where('id_empresa', $validated['id_empresa'])
                    ->where('nivel', $validated['nivel'] - 1)
                    ->where('en_uso', true)
                    ->orderBy('codigo_cuenta')
                    ->first();
                
                if ($madreAuto) {
                    $validated['id_cuenta_madre'] = $madreAuto->id_cuenta;
                }
            }

            // ✅ Crear la cuenta
            $cuenta = Cuenta::create($validated);
            
            return redirect()->route('cuentas.index', ['empresa_id' => $validated['id_empresa']])
                ->with('success', 'Cuenta "' . $cuenta->nombre_cuenta . '" creada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔥 CORREGIDO: Manejar correctamente los errores de validación
            $errors = $e->errors();
            $errorMessages = [];
            
            foreach ($errors as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }
            
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(' | ', $errorMessages))
                ->withInput();
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la cuenta: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function show(string $id)
    {
        if (!Gate::allows('ver-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para ver cuentas');
        }

        try {
            $cuenta = Cuenta::with(['empresa', 'cuentaMadre', 'cuentasHijas'])->findOrFail($id);
            
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $cuenta->id_empresa)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index')
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }
            
            return Inertia::render('Cuentas/Show', [
                'cuenta' => $cuenta,
                'flash' => $flash,
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para editar cuentas');
        }

        try {
            $user = auth()->user();
            $cuenta = Cuenta::with('cuentaMadre')->findOrFail($id);
            
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $cuenta->id_empresa)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index')
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            $userId = $user->id_usuario;
            
            $empresaIds = DB::table('empresas_usuarios')
                ->where('id_usuario', $userId)
                ->pluck('id_empresa')
                ->toArray();
            
            $empresas = Empresa::whereIn('id', $empresaIds)
                ->where('activo', true)
                ->get();
            
            $cuentasMadre = Cuenta::where('id_empresa', $cuenta->id_empresa)
                ->where('id_cuenta', '!=', $id)
                ->where('nivel', '<', $cuenta->nivel)
                ->where('en_uso', true)
                ->orderBy('codigo_cuenta')
                ->get()
                ->map(function($cuenta) {
                    return [
                        'id_cuenta' => $cuenta->id_cuenta,
                        'codigo_cuenta' => $cuenta->codigo_cuenta,
                        'nombre_cuenta' => $cuenta->nombre_cuenta,
                        'nivel' => $cuenta->nivel,
                        'display' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta . ' (Nivel ' . $cuenta->nivel . ')'
                    ];
                });
            
            $cuentasResultados = Cuenta::where('id_empresa', $cuenta->id_empresa)
                ->where('en_uso', true)
                ->where('es_cuenta_resultados', true)
                ->where('id_cuenta', '!=', $id)
                ->orderBy('codigo_cuenta')
                ->get()
                ->map(function($cuenta) {
                    return [
                        'id_cuenta' => $cuenta->id_cuenta,
                        'codigo_cuenta' => $cuenta->codigo_cuenta,
                        'nombre_cuenta' => $cuenta->nombre_cuenta,
                        'nivel' => $cuenta->nivel,
                        'display' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta . ' (Nivel ' . $cuenta->nivel . ')'
                    ];
                });
            
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }
            
            return Inertia::render('Cuentas/Edit', [
                'cuenta' => $cuenta,
                'empresas' => $empresas,
                'cuentasMadre' => $cuentasMadre,
                'cuentasResultados' => $cuentasResultados,
                'flash' => $flash,
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

  public function update(Request $request, string $id)
{
    if (!Gate::allows('crear-cuentas')) {
        return redirect()->route('cuentas.index')
            ->with('error', 'No tienes permiso para editar cuentas');
    }

    try {
        $cuenta = Cuenta::findOrFail($id);
        
        $user = auth()->user();
        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $cuenta->id_empresa)
            ->where('id_usuario', $user->id_usuario)
            ->exists();
            
        if (!$tieneAcceso) {
            return redirect()->back()
                ->with('error', 'No tienes acceso a esta cuenta')
                ->withInput();
        }

        // 🔥 PRIMERO: Normalizar los datos antes de la validación
        $data = $request->all();
        
        if (isset($data['cuenta_resultados'])) {
            if (is_array($data['cuenta_resultados']) && empty($data['cuenta_resultados'])) {
                $data['cuenta_resultados'] = null;
            } elseif (is_string($data['cuenta_resultados']) && trim($data['cuenta_resultados']) === '') {
                $data['cuenta_resultados'] = null;
            }
        }
        
        $request->merge($data);

        $validated = $request->validate([
            'codigo_cuenta' => 'required|string|max:50',
            'nombre_cuenta' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'Naturaleza' => 'required|in:DEUDORA,ACREEDORA',
            'id_cuenta_madre' => 'nullable|exists:cuentas,id_cuenta',
            'en_uso' => 'nullable|boolean',
            'es_cuenta_resultados' => 'nullable|boolean',
            'fondeo_c' => 'nullable|boolean',
            'saldo_inicial' => 'nullable|numeric',
            'nivel' => 'required|integer|min:0|max:5',
            'cuenta_resultados' => 'nullable|exists:cuentas,id_cuenta',
        ]);

        // Verificar que el código no esté duplicado
        $exists = Cuenta::where('id_empresa', $cuenta->id_empresa)
            ->where('codigo_cuenta', $validated['codigo_cuenta'])
            ->where('id_cuenta', '!=', $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->with('error', 'El código "' . $validated['codigo_cuenta'] . '" ya está en uso')
                ->withInput();
        }

        $validated['es_cuenta_resultados'] = $validated['es_cuenta_resultados'] ?? $cuenta->es_cuenta_resultados;
        $validated['fondeo_c'] = $validated['fondeo_c'] ?? $cuenta->fondeo_c;
        $validated['en_uso'] = $validated['en_uso'] ?? $cuenta->en_uso;

        if ($validated['es_cuenta_resultados']) {
            $validated['nivel'] = 2;
            $validated['id_cuenta_madre'] = null;
            $validated['cuenta_resultados'] = null;
            $validated['fondeo_c'] = false;
        } else {
            if ($validated['fondeo_c']) {
                $validated['cuenta_resultados'] = null;
            } else {
                $cuentaResultadosValue = $validated['cuenta_resultados'] ?? null;
                
                if (is_array($cuentaResultadosValue) && empty($cuentaResultadosValue)) {
                    $cuentaResultadosValue = null;
                } elseif (is_string($cuentaResultadosValue) && trim($cuentaResultadosValue) === '') {
                    $cuentaResultadosValue = null;
                }
                
                $validated['cuenta_resultados'] = $cuentaResultadosValue;
                
                if (empty($validated['cuenta_resultados'])) {
                    return redirect()->back()
                        ->with('error', 'Debes seleccionar una cuenta de resultados a la que pertenece esta cuenta')
                        ->withInput();
                }
                
                $cuentaResultados = Cuenta::find($validated['cuenta_resultados']);
                if (!$cuentaResultados || !$cuentaResultados->es_cuenta_resultados) {
                    return redirect()->back()
                        ->with('error', 'La cuenta seleccionada no es una cuenta de resultados válida')
                        ->withInput();
                }
            }

            if ($validated['nivel'] == 1) {
                $validated['id_cuenta_madre'] = null;
            }

            if ($validated['nivel'] > 1) {
                if (empty($validated['id_cuenta_madre'])) {
                    return redirect()->back()
                        ->with('error', 'Debes seleccionar una cuenta madre de nivel ' . ($validated['nivel'] - 1))
                        ->withInput();
                }
                
                $madre = Cuenta::find($validated['id_cuenta_madre']);
                if (!$madre) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre seleccionada no existe.')
                        ->withInput();
                }
                if ($madre->nivel != ($validated['nivel'] - 1)) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre debe ser de nivel ' . ($validated['nivel'] - 1))
                        ->withInput();
                }
                if ($madre->id_empresa != $cuenta->id_empresa) {
                    return redirect()->back()
                        ->with('error', 'La cuenta madre no pertenece a la misma empresa')
                        ->withInput();
                }
                if ($madre->id_cuenta == $id) {
                    return redirect()->back()
                        ->with('error', 'No puedes seleccionar esta cuenta como su propia madre')
                        ->withInput();
                }
            }
        }

        $cuenta->update($validated);

        return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
            ->with('success', 'Cuenta "' . $cuenta->nombre_cuenta . '" actualizada exitosamente.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // 🔥 CORREGIDO: Manejar correctamente los errores de validación
        $errors = $e->errors();
        $errorMessages = [];
        
        foreach ($errors as $field => $messages) {
            $errorMessages[] = $field . ': ' . implode(', ', $messages);
        }
        
        return redirect()->back()
            ->with('error', 'Error de validación: ' . implode(' | ', $errorMessages))
            ->withInput();
        
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al actualizar la cuenta: ' . $e->getMessage())
            ->withInput();
    }
}

    public function destroy(string $id)
    {
        if (!Gate::allows('eliminar-movimientos')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para eliminar cuentas');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $empresaId)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            if (!$cuenta->en_uso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('warning', 'La cuenta ya está inactiva');
            }

            $nombreCuenta = $cuenta->nombre_cuenta;
            $hijasCount = $cuenta->cuentasHijas()->count();
            
            $cuenta->update(['en_uso' => false]);

            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desactivar la cuenta: ' . $e->getMessage());
        }
    }

    public function inactivas(Request $request)
    {
        if (!Gate::allows('ver-cuentas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver cuentas',
                'data' => []
            ], 403);
        }

        $empresaId = $request->input('empresa_id');
        
        if (!$empresaId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere empresa_id',
                'data' => []
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
                'data' => []
            ], 401);
        }

        $tieneAcceso = DB::table('empresas_usuarios')
            ->where('id_empresa', $empresaId)
            ->where('id_usuario', $user->id_usuario)
            ->exists();
            
        if (!$tieneAcceso) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene acceso a esta empresa',
                'data' => []
            ], 403);
        }

        $cuentas = Cuenta::where('id_empresa', $empresaId)
            ->where('en_uso', false)
            ->orderBy('codigo_cuenta')
            ->get()
            ->map(function($cuenta) {
                $jerarquia = $cuenta->nivel_jerarquico;
                
                $nombreMadre = 'SIN MADRE';
                if ($cuenta->id_cuenta_madre) {
                    $madre = Cuenta::find($cuenta->id_cuenta_madre);
                    if ($madre) {
                        $nombreMadre = $madre->nombre_cuenta;
                    }
                }
                
                return [
                    'id_cuenta' => $cuenta->id_cuenta,
                    'codigo_cuenta' => $cuenta->codigo_cuenta,
                    'nombre_cuenta' => $cuenta->nombre_cuenta,
                    'nivel' => $cuenta->nivel,
                    'nivel_texto' => $cuenta->nivel_texto,
                    'nivel_jerarquico' => $jerarquia,
                    'indice' => $cuenta->codigo_cuenta . ' - ' . $cuenta->nombre_cuenta,
                    'cuenta_madre' => $nombreMadre,
                    'tipo_cuenta' => $cuenta->tipo_cuenta,
                    'Naturaleza' => $cuenta->Naturaleza,
                    'en_uso' => $cuenta->en_uso,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $cuentas,
            'total' => $cuentas->count(),
            'message' => 'Cuentas inactivas obtenidas exitosamente'
        ]);
    }

    public function restaurar(Request $request, string $id)
    {
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para restaurar cuentas');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $empresaId)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            if ($cuenta->en_uso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('warning', 'La cuenta ya está activa');
            }

            $cuenta->update(['en_uso' => true]);

            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al restaurar la cuenta: ' . $e->getMessage());
        }
    }

    public function toggleActive(Request $request, string $id)
    {
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para cambiar el estado de la cuenta');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $empresaId)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            $nuevoEstado = !$cuenta->en_uso;
            $cuenta->update(['en_uso' => $nuevoEstado]);
            
            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';
            $mensaje = 'Cuenta ' . $estadoTexto . ' exitosamente';
            
            if (!$nuevoEstado) {
                $hijasCount = $cuenta->cuentasHijas()->count();
                if ($hijasCount > 0) {
                    $mensaje .= '. La cuenta tiene ' . $hijasCount . ' cuenta(s) hija(s).';
                }
            }
            
            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                ->with('success', $mensaje);
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cambiarEstado(Request $request, string $id)
    {
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para cambiar el estado de la cuenta');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $cuenta->id_empresa)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            $nuevoEstado = !$cuenta->en_uso;
            $cuenta->en_uso = $nuevoEstado;
            $cuenta->save();

            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';
            $mensaje = 'Cuenta ' . $estadoTexto . ' exitosamente';
            
            if (!$nuevoEstado) {
                $hijasCount = $cuenta->cuentasHijas()->count();
                if ($hijasCount > 0) {
                    $mensaje .= '. La cuenta tiene ' . $hijasCount . ' cuenta(s) hija(s).';
                }
            }

            return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
                ->with('success', $mensaje);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cambiar el estado de la cuenta: ' . $e->getMessage());
        }
    }
}