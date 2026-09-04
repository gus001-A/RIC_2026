<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ✅ Verificar permiso para ver usuarios
        if (!Gate::allows('ver-usuarios')) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permiso para ver usuarios');
        }

        $search = $request->get('search');
        $tipo = $request->get('tipo');
        $estado = $request->get('estado');

        $usuarios = Usuario::query()
            ->with('empresas')
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nombre_completo', 'LIKE', "%{$search}%")
                    ->orWhere('nombre_usuario', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->when($tipo, function ($query, $tipo) {
                return $query->where('tipo_usuario', $tipo);
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                return $query->where('activo', $estado === 'activo');
            })
            ->orderBy('nombre_completo')
            ->paginate(10)
            ->withQueryString();

        // ✅ Transformar los datos para incluir el texto del tipo
        $usuarios->getCollection()->transform(function ($usuario) {
            $tipos = [
                'LECTOR' => 'Lector',
                'CAPTURISTA' => 'Capturista',
                'ADMINISTRADOR' => 'Administrador',
                'AUDITOR' => 'Auditor',
                'SUPERUSUARIO' => 'Super Usuario'
            ];
            $usuario->tipo_usuario_texto = $tipos[$usuario->tipo_usuario] ?? $usuario->tipo_usuario;
            return $usuario;
        });

        $stats = [
            'total' => Usuario::count(),
            'activos' => Usuario::where('activo', true)->count(),
            'inactivos' => Usuario::where('activo', false)->count(),
        ];

        $tipos = [
            'LECTOR' => 'Lector',
            'CAPTURISTA' => 'Capturista',
            'ADMINISTRADOR' => 'Administrador',
            'AUDITOR' => 'Auditor',
            'SUPERUSUARIO' => 'Super Usuario'
        ];

        // ✅ RECOPILAR TODOS LOS FLASH MESSAGES DE LA SESIÓN
        $flash = [];
        $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
        foreach ($flashTypes as $type) {
            if (session()->has($type)) {
                $flash[$type] = session($type);
            }
        }

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'stats' => $stats,
            'filtros' => [
                'search' => $search,
                'tipo' => $tipo,
                'estado' => $estado
            ],
            'tipos' => $tipos,
            'flash' => $flash // ✅ PASAR FLASH EXPLÍCITAMENTE
        ]);
    }

    /**
     * Resetear contraseña de un usuario
     */
    public function resetPassword(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar usuarios
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para resetear contraseñas');
        }

        try {
            $validated = $request->validate([
                'password' => 'required|string|min:8'
            ]);

            $usuario = Usuario::findOrFail($id);
            
            $nuevaPassword = $validated['password'];
            
            $usuario->update([
                'password_hash' => Hash::make($nuevaPassword)
            ]);

            return redirect()->route('usuarios.index')
                ->with('success', 'Contraseña actualizada correctamente')
                ->with('nueva_password', $nuevaPassword);

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Error al actualizar la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de un usuario (activar/desactivar)
     */
    public function toggleActivo(string $id)
    {
        // ✅ Verificar permiso para editar usuarios
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para cambiar el estado de usuarios');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            if ($usuario->id_usuario === auth()->id()) {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No puedes desactivar tu propio usuario');
            }

            $usuario->update([
                'activo' => !$usuario->activo
            ]);

    
            return redirect()->route('usuarios.index');

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Error al cambiar el estado del usuario');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ✅ Verificar permiso para crear usuarios
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para crear usuarios');
        }

        $empresas = Empresa::where('activo', true)
            ->orderBy('nombre_empresa')
            ->get(['id', 'nombre_empresa']);

        $etiquetas = [
            'LECTOR' => 'Lector',
            'CAPTURISTA' => 'Capturista',
            'ADMINISTRADOR' => 'Administrador',
            'AUDITOR' => 'Auditor',
            'SUPERUSUARIO' => 'Super Usuario'
        ];

        // Sólo los tipos que el rol actual puede crear.
        $permitidos = \App\Providers\AppServiceProvider::tiposUsuarioQuePuedeCrear(auth()->user()->tipo_usuario);
        $tipos = collect($permitidos)->mapWithKeys(fn ($t) => [$t => $etiquetas[$t] ?? $t])->all();

        // ✅ RECOPILAR FLASH MESSAGES
        $flash = [];
        $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
        foreach ($flashTypes as $type) {
            if (session()->has($type)) {
                $flash[$type] = session($type);
            }
        }

        return Inertia::render('Usuarios/Create', [
            'empresas' => $empresas,
            'tipos' => $tipos,
            'flash' => $flash // ✅ PASAR FLASH EXPLÍCITAMENTE
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Verificar permiso para crear usuarios
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para crear usuarios');
        }

        try {
            // Qué TIPOS de usuario puede crear el rol actual (según especificación):
            //  ADMINISTRADOR -> LECTOR, CAPTURISTA, ADMINISTRADOR
            //  AUDITOR       -> todos menos SUPERUSUARIO
            //  SUPERUSUARIO  -> todos
            $tiposPermitidos = \App\Providers\AppServiceProvider::tiposUsuarioQuePuedeCrear(auth()->user()->tipo_usuario);

            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:20',
                'tipo_usuario' => ['required', 'string', \Illuminate\Validation\Rule::in($tiposPermitidos)],
                'activo' => 'sometimes|boolean',
                'empresas' => 'sometimes|array',
                'empresas.*' => 'exists:empresas,id'
            ], [
                'tipo_usuario.in' => 'No tienes permiso para crear un usuario de ese tipo.',
            ]);

            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre_completo' => $validated['nombre_completo'],
                'nombre_usuario' => $validated['nombre_usuario'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'telefono' => $validated['telefono'] ?? null,
                'tipo_usuario' => $validated['tipo_usuario'],
                'activo' => $validated['activo'] ?? true,
            ]);

            if (!empty($validated['empresas'])) {
                $usuario->empresas()->attach($validated['empresas']);
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->with('error', 'Error de validación: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors())))
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // ✅ Verificar permiso para editar usuarios
        if (!Gate::allows('editar-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para editar usuarios');
        }

        try {
            $usuario = Usuario::with('empresas')->findOrFail($id);

            $empresas = Empresa::where('activo', true)
                ->orderBy('nombre_empresa')
                ->get(['id', 'nombre_empresa']);

            $etiquetas = [
                'LECTOR' => 'Lector',
                'CAPTURISTA' => 'Capturista',
                'ADMINISTRADOR' => 'Administrador',
                'AUDITOR' => 'Auditor',
                'SUPERUSUARIO' => 'Super Usuario'
            ];

            // Tipos que el rol actual puede asignar + el tipo actual del usuario
            // editado (para no ocultarlo del <select> si es superior).
            $permitidos = \App\Providers\AppServiceProvider::tiposUsuarioQuePuedeCrear(auth()->user()->tipo_usuario);
            $permitidos = array_values(array_unique(array_merge($permitidos, [$usuario->tipo_usuario])));
            $tipos = collect($permitidos)->mapWithKeys(fn ($t) => [$t => $etiquetas[$t] ?? $t])->all();

            // ✅ RECOPILAR FLASH MESSAGES
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }

            return Inertia::render('Usuarios/Edit', [
                'usuario' => $usuario,
                'empresas' => $empresas,
                'tipos' => $tipos,
                'empresasUsuario' => $usuario->empresas->pluck('id')->toArray(),
                'flash' => $flash // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Usuario no encontrado');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar usuarios
        if (!Gate::allows('editar-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para editar usuarios');
        }

        try {
            $usuarioEditado = Usuario::findOrFail($id);
            $tiposPermitidos = \App\Providers\AppServiceProvider::tiposUsuarioQuePuedeCrear(auth()->user()->tipo_usuario);
            // Permitir conservar el tipo actual del usuario aunque el editor no
            // pueda "crear" ese tipo (p. ej. admin editando datos de un auditor).
            $tiposPermitidos = array_values(array_unique(array_merge($tiposPermitidos, [$usuarioEditado->tipo_usuario])));

            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario,' . $id . ',id_usuario',
                'email' => 'required|email|max:255',
                // La contraseña es opcional al editar: sólo se cambia si se captura.
                'password' => 'nullable|string|min:8|confirmed',
                'telefono' => 'nullable|string|max:20',
                'tipo_usuario' => ['required', 'string', \Illuminate\Validation\Rule::in($tiposPermitidos)],
                'activo' => 'sometimes|boolean',
                'empresas' => 'sometimes|array',
                'empresas.*' => 'exists:empresas,id'
            ], [
                'tipo_usuario.in' => 'No tienes permiso para asignar ese tipo de usuario.',
            ]);

            DB::beginTransaction();

            $usuario = $usuarioEditado;

            $data = [
                'nombre_completo' => $validated['nombre_completo'],
                'nombre_usuario' => $validated['nombre_usuario'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'tipo_usuario' => $validated['tipo_usuario'],
                'activo' => $validated['activo'] ?? true,
            ];

            // Si viene password, actualizarla
            if (!empty($validated['password'] ?? null)) {
                $data['password_hash'] = Hash::make($validated['password']);
            }

            $usuario->update($data);

            // Sincronizar empresas
            if (isset($validated['empresas'])) {
                $usuario->empresas()->sync($validated['empresas']);
            } else {
                $usuario->empresas()->detach();
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->with('error', 'Error de validación: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors())))
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ELIMINACIÓN LÓGICA - Desactivar usuario (cambiar activo a false)
     */
    public function destroy(string $id)
    {
        // ✅ Verificar permiso para eliminar/desactivar usuarios
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para eliminar usuarios');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            // No permitir eliminar el propio usuario
            if ($usuario->id_usuario === auth()->id()) {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No puedes eliminar tu propio usuario');
            }

            // Si ya está inactivo, mostrar mensaje
            if (!$usuario->activo) {
                return redirect()->route('usuarios.index')
                    ->with('warning', 'El usuario ya está eliminado');
            }

            $nombre = $usuario->nombre_completo;

            // ELIMINACIÓN LÓGICA: actualizar el campo activo a false
            $usuario->activo = false;
            $usuario->save();

            return redirect()->route('usuarios.index');

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * RESTAURAR usuario inactivo (activarlo de nuevo)
     */
    public function restore(string $id)
    {
        // ✅ Verificar permiso para editar usuarios (restaurar es una forma de editar)
        if (!Gate::allows('crear-usuarios')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para restaurar usuarios');
        }

        try {
            $usuario = Usuario::findOrFail($id);

            // Verificar si ya está activo
            if ($usuario->activo) {
                return redirect()->route('usuarios.index')
                    ->with('warning', 'El usuario ya está activo');
            }

            $nombre = $usuario->nombre_completo;

            // Restaurar: activar de nuevo
            $usuario->activo = true;
            $usuario->save();
            return redirect()->route('usuarios.index');

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Error al restaurar el usuario: ' . $e->getMessage());
        }
    }
}