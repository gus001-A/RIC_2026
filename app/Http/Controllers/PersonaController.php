<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\DocumentoPersona;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ✅ Verificar permiso para ver personas
        if (!Gate::allows('ver-personas')) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permiso para ver personas');
        }

        try {
            $query = Persona::query();

            // Búsqueda
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtro por tipo de persona
            if ($request->filled('tipo_persona')) {
                $query->where('tipo_persona', $request->tipo_persona);
            }

            // Filtro de estado (activo/inactivo) - SOLO UNA VEZ
            if ($request->filled('estado')) {
                $estado = $request->estado;
                if ($estado === 'activo') {
                    $query->where('activo', true);
                } elseif ($estado === 'inactivo') {
                    $query->where('activo', false);
                }
            }

            // Filtro por ciudad
            if ($request->filled('ciudad')) {
                $query->where('ciudad', $request->ciudad);
            }

            // Filtro por representante - CORREGIDO (son campos directos, no relación)
            if ($request->filled('representante')) {
                $query->where(function($q) use ($request) {
                    $q->where('representante_nombre', 'LIKE', '%' . $request->representante . '%')
                    ->orWhere('representante_paterno', 'LIKE', '%' . $request->representante . '%')
                    ->orWhere('representante_materno', 'LIKE', '%' . $request->representante . '%');
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'id_persona');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = ['id_persona', 'Nombre', 'Paterno', 'rfc', 'email', 'created_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('id_persona', 'desc');
            }

            $perPage = $request->get('per_page', 15);
            $personas = $query->paginate($perPage)->withQueryString();

            // ESTADÍSTICAS ACTUALIZADAS
            $stats = [
                'total' => Persona::count(),
                'fisicas' => Persona::where('tipo_persona', 'FISICA')->count(),
                'morales' => Persona::where('tipo_persona', 'MORAL')->count(),
                'activas' => Persona::where('activo', true)->count(),
                'inactivas' => Persona::where('activo', false)->count(),
            ];

            // ✅ RECOPILAR FLASH MESSAGES
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }

            return Inertia::render('Personas/Index', [
                'personas' => $personas,
                'stats' => $stats,
                'filtros' => $request->only(['search', 'tipo_persona', 'estado', 'ciudad', 'representante']),
                'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Personas/Index', [
                'personas' => (object) ['data' => [], 'links' => [], 'total' => 0, 'from' => 0, 'to' => 0],
                'stats' => ['total' => 0, 'fisicas' => 0, 'morales' => 0, 'activas' => 0, 'inactivas' => 0],
                'filtros' => [],
                'flash' => session()->get('flash') ?? [],
                'error' => 'Error al cargar las personas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ✅ Verificar permiso para crear personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para crear personas');
        }

        // ✅ RECOPILAR FLASH MESSAGES
        $flash = [];
        $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
        foreach ($flashTypes as $type) {
            if (session()->has($type)) {
                $flash[$type] = session($type);
            }
        }

        return Inertia::render('Personas/Create', [
            'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Verificar permiso para crear personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para crear personas');
        }

        try {
            $validated = $this->validatePersona($request);

            // Construir el array de datos
            $data = $this->buildPersonaData($validated);

            $persona = Persona::create($data);

            return redirect()->route('personas.index')
                ->with('success', 'Persona creada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la persona: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ✅ Verificar permiso para ver personas
        if (!Gate::allows('ver-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para ver personas');
        }

        try {
            $persona = Persona::with(['polizas', 'documentos'])->findOrFail($id);

            // ✅ RECOPILAR FLASH MESSAGES
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }

            return Inertia::render('Personas/Show', [
                'persona' => $persona,
                'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('personas.index')
                ->with('error', 'Persona no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('personas.index')
                ->with('error', 'Error al cargar la persona: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // ✅ Verificar permiso para crear/editar personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para editar personas');
        }

        try {
            $persona = Persona::findOrFail($id);

            // ✅ RECOPILAR FLASH MESSAGES
            $flash = [];
            $flashTypes = ['success', 'error', 'info', 'warning', 'updated', 'created', 'deleted'];
            foreach ($flashTypes as $type) {
                if (session()->has($type)) {
                    $flash[$type] = session($type);
                }
            }

            return Inertia::render('Personas/Edit', [
                'persona' => $persona,
                'flash' => $flash, // ✅ PASAR FLASH EXPLÍCITAMENTE
            ]);

        } catch (ModelNotFoundException $e) {
            return redirect()->route('personas.index')
                ->with('error', 'Persona no encontrada');
        } catch (\Exception $e) {
            return redirect()->route('personas.index')
                ->with('error', 'Error al cargar la persona: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para editar personas');
        }

        try {
            $persona = Persona::findOrFail($id);

            $validated = $this->validatePersona($request, $persona->id_persona);

            // Construir el array de datos
            $data = $this->buildPersonaData($validated);

            $persona->update($data);

            return redirect()->route('personas.index')
                ->with('success', 'Persona actualizada exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Persona no encontrada')
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la persona: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (Soft Delete / Logical Deletion)
     */
    public function destroy(string $id)
    {
        // ✅ Verificar permiso para eliminar personas (usamos el mismo que eliminar movimientos)
        if (!Gate::allows('eliminar-movimientos')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para eliminar personas');
        }

        try {
            $persona = Persona::findOrFail($id);
            
            // Verificar si ya está inactiva
            if (!$persona->activo) {
                return redirect()->back()
                    ->with('warning', 'La persona ya está inactiva');
            }

            // Verificar si tiene pólizas o documentos asociados
            $polizasCount = $persona->polizas()->count();
            $documentosCount = $persona->documentos()->count();
            
            if ($polizasCount > 0 || $documentosCount > 0) {
                $mensaje = 'No se puede desactivar la persona porque tiene ';
                $partes = [];
                
                if ($polizasCount > 0) {
                    $partes[] = $polizasCount . ' póliza(s)';
                }
                if ($documentosCount > 0) {
                    $partes[] = $documentosCount . ' documento(s)';
                }
                
                $mensaje .= implode(' y ', $partes) . ' asociados';
                
                return redirect()->back()
                    ->with('error', $mensaje);
            }

            // 🟢 ELIMINACIÓN LÓGICA: Cambiar activo a false
            $persona->update(['activo' => false]);

            return redirect()->route('personas.index')
                ->with('success', 'Persona desactivada exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Persona no encontrada');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desactivar la persona: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status of the specified resource.
     */
    public function toggleActive(Request $request, string $id)
    {
        // ✅ Verificar permiso para editar personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->route('personas.index')
                ->with('error', 'No tienes permiso para cambiar el estado de la persona');
        }

        try {
            $persona = Persona::findOrFail($id);
            
            // Si está activo, verificar relaciones
            if ($persona->activo) {
                $polizasCount = $persona->polizas()->count();
                $documentosCount = $persona->documentos()->count();
                
                if ($polizasCount > 0 || $documentosCount > 0) {
                    $mensaje = 'No se puede desactivar la persona porque tiene ';
                    $partes = [];
                    
                    if ($polizasCount > 0) {
                        $partes[] = $polizasCount . ' póliza(s)';
                    }
                    if ($documentosCount > 0) {
                        $partes[] = $documentosCount . ' documento(s)';
                    }
                    
                    $mensaje .= implode(' y ', $partes) . ' asociados';
                    
                    return redirect()->back()
                        ->with('error', $mensaje);
                }
            }
            
            $nuevoEstado = !$persona->activo;
            $persona->update(['activo' => $nuevoEstado]);
            
            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';
            
            return redirect()->back()
                ->with('success', "Persona {$estadoTexto} exitosamente");
                
        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Persona no encontrada');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Generate RFC automatically based on person data
     */
    public function generarRFC(Request $request)
    {
        // ✅ Verificar permiso para ver/crear personas (generar RFC es una función auxiliar)
        if (!Gate::allows('ver-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para generar RFC'
            ], 403);
        }

        try {
            $request->validate([
                'tipo_persona' => 'required|in:FISICA,MORAL',
                'Nombre' => 'required|string|max:255',
                'Paterno' => 'nullable|string|max:255',
                'Materno' => 'nullable|string|max:255',
                'Fecha_nacimiento' => 'required|date',
            ]);

            $rfc = $this->calcularRFC(
                $request->tipo_persona,
                $request->Nombre,
                $request->Paterno,
                $request->Materno,
                $request->Fecha_nacimiento
            );

            return response()->json([
                'success' => true,
                'rfc' => $rfc,
                'message' => 'RFC generado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el RFC: ' . $e->getMessage()
            ], 422);
        }
    }

    // ============================================
    // 🟢 MÉTODOS PARA DOCUMENTOS
    // ============================================

    /**
     * Obtener documentos de una persona
     */
    public function getDocumentos($id)
    {
        // ✅ Verificar permiso para ver personas (y sus documentos)
        if (!Gate::allows('ver-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver documentos'
            ], 403);
        }

        try {
            $persona = Persona::findOrFail($id);
            $documentos = DocumentoPersona::byPersona($id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($doc) {
                    $doc->tipo_documento_texto = $this->getTipoDocumentoTexto($doc->tipo_documento);
                    return $doc;
                });

            return response()->json([
                'success' => true,
                'data' => $documentos,
                'persona' => $persona->nombre_completo,
                'total' => $documentos->count(),
                'message' => 'Documentos obtenidos exitosamente'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir un documento para una persona
     */
    public function subirDocumento(Request $request, $id)
    {
        // ✅ Verificar permiso para editar personas (subir documentos es parte de editar)
        if (!Gate::allows('crear-personas')) {
            return redirect()->back()
                ->with('error', 'No tienes permiso para subir documentos');
        }

        try {
            $persona = Persona::findOrFail($id);

            $request->validate([
                'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'tipo_documento' => 'required|string|in:INE,RFC,CURP,COMPROBANTE,OTRO',
                'titulo' => 'nullable|string|max:255',
                'finalizado' => 'nullable|boolean',
            ]);

            $file = $request->file('documento');
            $extension = $file->getClientOriginalExtension();
            
            $nombreGuardar = time() . '_' . uniqid() . '.' . $extension;
            $rutaRelativa = "documentos/personas/{$id}/{$nombreGuardar}";
            
            $path = $file->storeAs(
                "documentos/personas/{$id}",
                $nombreGuardar,
                'public'
            );

            $documento = DocumentoPersona::create([
                'id_persona' => $id,
                'titulo' => $request->titulo ?? $this->getTipoDocumentoTexto($request->tipo_documento) . ' - ' . $persona->nombre_completo,
                'tipo_documento' => $request->tipo_documento,
                'ruta_archivo' => $rutaRelativa,
                'fecha_subida' => now(),
                'finalizado' => $request->boolean('finalizado', false),
                'id_usuario_subio' => Auth::id() ?? 1,
            ]);

            return redirect()->back()
                ->with('success', 'Documento subido exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Persona no encontrada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Error de validación: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al subir el documento: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Descargar un documento
     */
    public function descargarDocumento($id)
    {
        // ✅ Verificar permiso para ver personas (y descargar sus documentos)
        if (!Gate::allows('ver-personas')) {
            return redirect()->back()
                ->with('error', 'No tienes permiso para descargar documentos');
        }

        try {
            $documento = DocumentoPersona::findOrFail($id);
            
            if (!Storage::disk('public')->exists($documento->ruta_archivo)) {
                return redirect()->back()
                    ->with('error', 'El archivo no existe en el servidor');
            }

            $nombreArchivo = $documento->titulo . '.' . pathinfo($documento->ruta_archivo, PATHINFO_EXTENSION);
            return Storage::disk('public')->download($documento->ruta_archivo, $nombreArchivo);

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Documento no encontrado');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un documento
     */
    public function eliminarDocumento($id)
    {
        // ✅ Verificar permiso para editar personas (eliminar documentos es parte de editar)
        if (!Gate::allows('crear-personas')) {
            return redirect()->back()
                ->with('error', 'No tienes permiso para eliminar documentos');
        }

        try {
            $documento = DocumentoPersona::findOrFail($id);
            
            if (Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            $documento->delete();

            return redirect()->back()
                ->with('success', 'Documento eliminado exitosamente');

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Documento no encontrado');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Marcar documento como finalizado o pendiente
     */
    public function toggleFinalizado($id)
    {
        // ✅ Verificar permiso para editar personas
        if (!Gate::allows('crear-personas')) {
            return redirect()->back()
                ->with('error', 'No tienes permiso para actualizar documentos');
        }

        try {
            $documento = DocumentoPersona::findOrFail($id);
            $documento->update(['finalizado' => !$documento->finalizado]);

            $estado = $documento->finalizado ? 'finalizado' : 'pendiente';
            return redirect()->back()
                ->with('success', "Documento marcado como {$estado}");

        } catch (ModelNotFoundException $e) {
            return redirect()->back()
                ->with('error', 'Documento no encontrado');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el documento: ' . $e->getMessage());
        }
    }

    // ============================================
    // 🟢 MÉTODOS PRIVADOS DE APOYO
    // ============================================

    /**
     * Validar los datos de la persona - MISMOS CAMPOS PARA AMBOS TIPOS
     */
    private function validatePersona(Request $request, $id = null)
    {
        $rules = [
            // Tipo de persona (solo para saber si es física o moral)
            'tipo_persona' => ['required', 'in:FISICA,MORAL'],
            'activo' => 'boolean',
            
            // MISMOS CAMPOS PARA AMBOS TIPOS
            'Nombre' => 'required|string|max:200',
            'Paterno' => 'required|string|max:100',
            'Materno' => 'nullable|string|max:100',
            'Fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:MASCULINO,FEMENINO,NO_ESPECIFICADO',
            
            // Datos fiscales y contacto
            'rfc' => 'nullable|string|max:20|unique:personas,rfc,' . $id . ',id_persona',
            'curp' => 'nullable|string|max:18|unique:personas,curp,' . $id . ',id_persona',
            'email' => 'nullable|email|max:100|unique:personas,email,' . $id . ',id_persona',
            'telefono_particular' => 'nullable|string|max:20',
            'telefono_trabajo' => 'nullable|string|max:20',
            'extension_trabajo' => 'nullable|string|max:10',
            
            // Dirección
            'direccion' => 'nullable|string',
            'calle' => 'nullable|string|max:150',
            'numero_exterior' => 'nullable|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            
            // Representante Legal (para ambos tipos)
            'representante_nombre' => 'nullable|string|max:100',
            'representante_paterno' => 'nullable|string|max:100',
            'representante_materno' => 'nullable|string|max:100',
            'representante_fecha_nacimiento' => 'nullable|date|before:today',
            'representante_sexo' => 'nullable|in:MASCULINO,FEMENINO,NO_ESPECIFICADO',
            'representante_email' => 'nullable|email|max:100',
            'representante_telefono_particular' => 'nullable|string|max:20',
            'representante_telefono_trabajo' => 'nullable|string|max:20',
            'representante_extension_trabajo' => 'nullable|string|max:10',
            
            // Notas
            'notas' => 'nullable|string',
        ];

        // Si se captura el representante, algunos campos se vuelven obligatorios
        if ($request->has('representante_nombre') && $request->representante_nombre) {
            $rules['representante_paterno'] = 'required|string|max:100';
            $rules['representante_fecha_nacimiento'] = 'required|date|before:today';
        }

        $messages = [
            'Nombre.required' => 'El nombre es obligatorio',
            'Paterno.required' => 'El apellido paterno es obligatorio',
            'Fecha_nacimiento.required' => 'La fecha es obligatoria',
            'Fecha_nacimiento.before' => 'La fecha debe ser anterior a hoy',
            'sexo.required' => 'El sexo es obligatorio',
            'rfc.unique' => 'Este RFC ya está registrado en el sistema',
            'curp.unique' => 'Este CURP ya está registrado en el sistema',
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema',
            'representante_paterno.required' => 'El apellido paterno del representante es obligatorio',
            'representante_fecha_nacimiento.required' => 'La fecha de nacimiento del representante es obligatoria',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Construir el array de datos para crear/actualizar
     */
    private function buildPersonaData($validated)
    {
        $data = [
            'tipo_persona' => $validated['tipo_persona'],
            'activo' => $validated['activo'] ?? true,
            
            // MISMOS CAMPOS PARA AMBOS TIPOS
            'Nombre' => $validated['Nombre'] ?? null,
            'Paterno' => $validated['Paterno'] ?? null,
            'Materno' => $validated['Materno'] ?? null,
            'Fecha_nacimiento' => $validated['Fecha_nacimiento'] ?? null,
            'sexo' => $validated['sexo'] ?? 'NO_ESPECIFICADO',
            
            // Datos fiscales y contacto
            'rfc' => $validated['rfc'] ?? null,
            'curp' => $validated['curp'] ?? null,
            'email' => $validated['email'] ?? null,
            'telefono_particular' => $validated['telefono_particular'] ?? null,
            'telefono_trabajo' => $validated['telefono_trabajo'] ?? null,
            'extension_trabajo' => $validated['extension_trabajo'] ?? null,
            
            // Dirección
            'direccion' => $validated['direccion'] ?? null,
            'calle' => $validated['calle'] ?? null,
            'numero_exterior' => $validated['numero_exterior'] ?? null,
            'numero_interior' => $validated['numero_interior'] ?? null,
            'colonia' => $validated['colonia'] ?? null,
            'ciudad' => $validated['ciudad'] ?? null,
            'municipio' => $validated['municipio'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'codigo_postal' => $validated['codigo_postal'] ?? null,
            
            // Representante Legal
            'representante_nombre' => $validated['representante_nombre'] ?? null,
            'representante_paterno' => $validated['representante_paterno'] ?? null,
            'representante_materno' => $validated['representante_materno'] ?? null,
            'representante_fecha_nacimiento' => $validated['representante_fecha_nacimiento'] ?? null,
            'representante_sexo' => $validated['representante_sexo'] ?? 'NO_ESPECIFICADO',
            'representante_email' => $validated['representante_email'] ?? null,
            'representante_telefono_particular' => $validated['representante_telefono_particular'] ?? null,
            'representante_telefono_trabajo' => $validated['representante_telefono_trabajo'] ?? null,
            'representante_extension_trabajo' => $validated['representante_extension_trabajo'] ?? null,
            
            // Notas
            'notas' => $validated['notas'] ?? null,
        ];

        return $data;
    }

    /**
     * Obtener texto del tipo de documento
     */
    private function getTipoDocumentoTexto($tipo)
    {
        $tipos = [
            'INE' => 'INE',
            'RFC' => 'RFC',
            'CURP' => 'CURP',
            'COMPROBANTE' => 'Comprobante de Domicilio',
            'OTRO' => 'Otro Documento'
        ];
        return $tipos[$tipo] ?? $tipo;
    }

    // ============================================
    // 🟢 MÉTODOS PARA RFC
    // ============================================

    /**
     * Calculate RFC according to SAT formula
     */
    private function calcularRFC($tipoPersona, $nombre, $apellidoPaterno, $apellidoMaterno, $fechaNacimiento)
    {
        $nombre = strtoupper(trim($nombre));
        $apellidoPaterno = strtoupper(trim($apellidoPaterno ?? ''));
        $apellidoMaterno = strtoupper(trim($apellidoMaterno ?? ''));
        
        if ($tipoPersona === 'FISICA') {
            $primerNombre = $this->obtenerPrimerNombre($nombre);
            $primerNombre = $this->limpiarTexto($primerNombre);
            $apellidoPaterno = $this->limpiarTexto($apellidoPaterno);
            $apellidoMaterno = $this->limpiarTexto($apellidoMaterno);
            
            $rfc = '';
            $rfc .= substr($apellidoPaterno, 0, 1);
            $vocal = $this->obtenerPrimeraVocalInterna($apellidoPaterno);
            $rfc .= $vocal ?: 'X';
            $rfc .= substr($apellidoMaterno, 0, 1) ?: 'X';
            $rfc .= substr($primerNombre, 0, 1);
            $fecha = new \DateTime($fechaNacimiento);
            $rfc .= $fecha->format('ymd');
            $rfc .= $this->generarHomoclave($apellidoPaterno . $apellidoMaterno . $primerNombre);
            
            return $rfc;
        } else {
            // Persona Moral - usa el nombre completo como razón social
            $razonSocial = $this->limpiarTexto($nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno);
            $rfc = '';
            $rfc .= substr($razonSocial, 0, 1);
            $segundaLetra = $this->obtenerSegundaLetra($razonSocial);
            $rfc .= $segundaLetra ?: 'X';
            $terceraLetra = $this->obtenerTerceraLetra($razonSocial);
            $rfc .= $terceraLetra ?: 'X';
            $fecha = new \DateTime($fechaNacimiento);
            $rfc .= $fecha->format('ymd');
            $rfc .= $this->generarHomoclave($razonSocial);
            
            return $rfc;
        }
    }

    private function obtenerPrimerNombre($nombreCompleto)
    {
        $partes = explode(' ', trim($nombreCompleto));
        $palabrasIgnorar = ['DE', 'LA', 'LAS', 'LOS', 'Y', 'DEL', 'EL', 'MI', 'SUS', 'CON', 'POR', 'PARA'];
        foreach ($partes as $parte) {
            if (!in_array($parte, $palabrasIgnorar) && strlen($parte) > 1) {
                return $parte;
            }
        }
        return $partes[0] ?? '';
    }

    private function limpiarTexto($texto)
    {
        $acentos = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U',
            'Ñ' => 'X', 'ñ' => 'X', 'Ü' => 'U', 'ü' => 'U'
        ];
        $texto = strtr($texto, $acentos);
        $texto = preg_replace('/[^A-Z]/', '', $texto);
        
        $palabrasComunes = ['DE', 'LA', 'LAS', 'LOS', 'Y', 'DEL', 'EL', 'MI', 'SUS', 'CON', 'POR', 'PARA'];
        foreach ($palabrasComunes as $palabra) {
            $texto = str_replace($palabra, '', $texto);
        }
        
        return $texto;
    }

    private function obtenerPrimeraVocalInterna($texto)
    {
        $vocales = ['A', 'E', 'I', 'O', 'U'];
        $longitud = strlen($texto);
        for ($i = 1; $i < $longitud; $i++) {
            if (in_array($texto[$i], $vocales)) {
                return $texto[$i];
            }
        }
        return null;
    }

    private function obtenerSegundaLetra($texto)
    {
        return strlen($texto) >= 2 ? $texto[1] : null;
    }

    private function obtenerTerceraLetra($texto)
    {
        return strlen($texto) >= 3 ? $texto[2] : null;
    }

    private function generarHomoclave($base)
    {
        $base = preg_replace('/[^A-Z0-9]/', '', $base);
        if (empty($base)) return 'XXX';
        $homoclave = substr($base, -3);
        if (strlen($homoclave) < 3) {
            $homoclave = str_pad($homoclave, 3, 'X');
        }
        return $homoclave;
    }
}