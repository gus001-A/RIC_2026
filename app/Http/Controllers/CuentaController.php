<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CuentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ✅ Verificar permiso para ver cuentas
        if (!Gate::allows('ver-cuentas')) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permiso para ver cuentas');
        }

        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $userId = $user->id_usuario;
        
        // Obtener empresas del usuario
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
        
        $empresaId = $request->input('empresa_id');
        
        if (!$empresaId && $empresas->count() > 0) {
            $empresaId = $empresas->first()->id;
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
                    'total' => Cuenta::where('id_empresa', $empresaId)->count(),
                    'deudoras' => Cuenta::where('id_empresa', $empresaId)
                        ->where('Naturaleza', 'DEUDORA')
                        ->count(),
                    'acreedoras' => Cuenta::where('id_empresa', $empresaId)
                        ->where('Naturaleza', 'ACREEDORA')
                        ->count(),
                ];

                $query = Cuenta::where('id_empresa', $empresaId);

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

                $query->orderBy('nivel')
                    ->orderBy('codigo_cuenta');

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
                    ];
                })->toArray();
            }
        }

        // ✅ RECOPILAR FLASH MESSAGES
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
            'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
        ]);
    }

    /**
     * Get nivel texto
     */
    private function getNivelTexto($nivel)
    {
        $niveles = ['', 'Primer Nivel', 'Segundo Nivel', 'Tercer Nivel', 'Cuarto Nivel', 'Quinto Nivel'];
        return $niveles[$nivel] ?? "Nivel {$nivel}";
    }

    /**
     * Get cuentas for DataTable (Server Side)
     */
    public function getCuentas(Request $request)
    {
        // ✅ Verificar permiso para ver cuentas (datatable)
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

        $query = Cuenta::where('id_empresa', $empresa_id);

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

    /**
     * Obtener cuentas madre para el select
     */
    public function getCuentasMadre(Request $request)
    {
        // ✅ Verificar permiso para ver cuentas (necesario para crear/editar)
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
            ->where('en_uso', 1)
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ✅ Verificar permiso para crear cuentas
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
        
        // ✅ RECOPILAR FLASH MESSAGES
        $flash = [];
        $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
        foreach ($flashTypes as $type) {
            if (session()->has($type)) {
                $flash[$type] = session($type);
            }
        }
        
        return Inertia::render('Cuentas/Create', [
            'empresas' => $empresas,
            'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Verificar permiso para crear cuentas
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para crear cuentas');
        }

        try {
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
                'tipo_cuenta' => 'required|in:FONDEADORA,RESULTADO,ORDEN',
                'id_cuenta_madre' => 'nullable|exists:cuentas,id_cuenta',
                'en_uso' => 'nullable|boolean',
                'es_cuenta_resultados' => 'nullable|boolean',
                'fondeo_c' => 'nullable|boolean',
                'saldo_inicial' => 'nullable|numeric',
                'nivel' => 'required|integer|min:0|max:5',
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

            $validated['saldo_inicial'] = $validated['saldo_inicial'] ?? 0;
            $validated['en_uso'] = $validated['en_uso'] ?? true;
            $validated['es_cuenta_resultados'] = $validated['es_cuenta_resultados'] ?? false;
            $validated['fondeo_c'] = $validated['fondeo_c'] ?? false;

            if ($validated['nivel'] == 1) {
                $validated['id_cuenta_madre'] = null;
            }

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
            }

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

            $cuenta = Cuenta::create($validated);
            
            return redirect()->route('cuentas.index', ['empresa_id' => $validated['id_empresa']])
                ->with('success', 'Cuenta "' . $cuenta->nombre_cuenta . '" creada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la cuenta: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ✅ Verificar permiso para ver cuentas
        if (!Gate::allows('ver-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para ver cuentas');
        }

        try {
            $cuenta = Cuenta::with(['empresa', 'cuentaMadre', 'cuentasHijas'])->findOrFail($id);
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $cuenta->id_empresa)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index')
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            // ✅ RECOPILAR FLASH MESSAGES
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }
            
            return Inertia::render('Cuentas/Show', [
                'cuenta' => $cuenta,
                'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // ✅ Verificar permiso para editar cuentas
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para editar cuentas');
        }

        try {
            $user = auth()->user();
            $cuenta = Cuenta::with('cuentaMadre')->findOrFail($id);
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
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
            
            // ✅ RECOPILAR FLASH MESSAGES
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
                'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Error al cargar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar cuentas
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para editar cuentas');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
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

            $validated = $request->validate([
                'codigo_cuenta' => 'required|string|max:50',
                'nombre_cuenta' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'Naturaleza' => 'required|in:DEUDORA,ACREEDORA',
                'tipo_cuenta' => 'required|in:FONDEADORA,RESULTADO,ORDEN',
                'id_cuenta_madre' => 'nullable|exists:cuentas,id_cuenta',
                'en_uso' => 'nullable|boolean',
                'es_cuenta_resultados' => 'nullable|boolean',
                'fondeo_c' => 'nullable|boolean',
                'saldo_inicial' => 'nullable|numeric',
                'nivel' => 'required|integer|min:0|max:5',
            ]);

            $validated['en_uso'] = $validated['en_uso'] ?? $cuenta->en_uso;
            $validated['es_cuenta_resultados'] = $validated['es_cuenta_resultados'] ?? $cuenta->es_cuenta_resultados;
            $validated['fondeo_c'] = $validated['fondeo_c'] ?? $cuenta->fondeo_c;

            $cuenta->update($validated);

            return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
                ->with('success', 'Cuenta actualizada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la cuenta: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminación lógica - Desactivar cuenta (cambia en_uso a false)
     */
    public function destroy(string $id)
    {
        // ✅ Verificar permiso para eliminar cuentas
        if (!Gate::allows('eliminar-movimientos')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para eliminar cuentas');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
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

            $hijasCount = $cuenta->cuentasHijas()->count();
            
            if ($hijasCount > 0) {
                return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                    ->with('error', 'No se puede desactivar la cuenta porque tiene ' . $hijasCount . ' cuenta(s) hija(s) asociadas');
            }

            $cuenta->update(['en_uso' => false]);

            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                ->with('success', 'Cuenta desactivada exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desactivar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cuentas inactivas
     */
    public function inactivas(Request $request)
    {
        // ✅ Verificar permiso para ver cuentas
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

    /**
     * Restaurar cuenta (activar)
     */
    public function restaurar(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar cuentas (restaurar es una forma de editar)
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para restaurar cuentas');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
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

            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                ->with('success', 'Cuenta restaurada exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al restaurar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Activar/Desactivar cuenta (Toggle)
     */
    public function toggleActive(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar cuentas (toggle es una forma de editar)
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para cambiar el estado de la cuenta');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            $empresaId = $cuenta->id_empresa;
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
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
                $hijasCount = $cuenta->cuentasHijas()->count();
                
                if ($hijasCount > 0) {
                    return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                        ->with('error', 'No se puede desactivar la cuenta porque tiene ' . $hijasCount . ' cuenta(s) hija(s) asociadas');
                }
            }
            
            $nuevoEstado = !$cuenta->en_uso;
            $cuenta->update(['en_uso' => $nuevoEstado]);
            
            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';
            
            return redirect()->route('cuentas.index', ['empresa_id' => $empresaId])
                ->with('success', 'Cuenta ' . $estadoTexto . ' exitosamente');
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de la cuenta (activar/desactivar)
     */
    public function cambiarEstado(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar cuentas
        if (!Gate::allows('crear-cuentas')) {
            return redirect()->route('cuentas.index')
                ->with('error', 'No tienes permiso para cambiar el estado de la cuenta');
        }

        try {
            $cuenta = Cuenta::findOrFail($id);
            
            // ✅ Verificar que el usuario tiene acceso a la empresa de la cuenta
            $user = auth()->user();
            $tieneAcceso = DB::table('empresas_usuarios')
                ->where('id_empresa', $cuenta->id_empresa)
                ->where('id_usuario', $user->id_usuario)
                ->exists();
                
            if (!$tieneAcceso) {
                return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
                    ->with('error', 'No tienes acceso a esta cuenta');
            }
            
            $cuenta->en_uso = !$cuenta->en_uso;
            $cuenta->save();

            return redirect()->route('cuentas.index', ['empresa_id' => $cuenta->id_empresa])
                ->with('success', 'Estado de la cuenta actualizado exitosamente.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('cuentas.index')
                ->with('error', 'Cuenta no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cambiar el estado de la cuenta: ' . $e->getMessage());
        }
    }
}