<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PreguntaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.create'])) {
                $this->logAccessDenied('preguntas.create', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para agregar preguntas.');
            }

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.create', ['Superadmin', 'Admin'], ['preguntas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            Log::info('Acceso exitoso a agregar preguntas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'preguntas_existentes' => $encuesta->preguntas->count()
            ]);

            return view('encuestas.preguntas.create', compact('encuesta'));
        } catch (Exception $e) {
            Log::error('Error accediendo a agregar preguntas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.index')
                ->with('error', 'Error al cargar la página de preguntas: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $encuestaId)
    {
        try {
            // Log de inicio
            Log::info('Iniciando creación de pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'data' => $request->all()
            ]);

            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.store'])) {
                $this->logAccessDenied('preguntas.store', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para guardar preguntas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.store', ['Superadmin', 'Admin'], ['preguntas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            $request->validate([
                'texto' => 'required|string|max:500|min:3',
                'descripcion' => 'nullable|string|max:1000',
                'placeholder' => 'nullable|string|max:255',
                'tipo' => 'required|in:respuesta_corta,parrafo,seleccion_unica,casillas_verificacion,lista_desplegable,escala_lineal,cuadricula_opcion_multiple,cuadricula_casillas,fecha,hora,carga_archivos,ubicacion_mapa,logica_condicional',
                'orden' => 'required|integer|min:1',
                'obligatoria' => 'nullable|in:on,1,true',
                'min_caracteres' => 'nullable|integer|min:0',
                'max_caracteres' => 'nullable|integer|min:1',
                'escala_min' => 'nullable|integer',
                'escala_max' => 'nullable|integer|gt:escala_min',
                'escala_etiqueta_min' => 'nullable|string|max:100',
                'escala_etiqueta_max' => 'nullable|string|max:100',
                'tipos_archivo_permitidos' => 'nullable|string|max:255',
                'tamano_max_archivo' => 'nullable|integer|min:1|max:100',
                'latitud_default' => 'nullable|numeric|between:-90,90',
                'longitud_default' => 'nullable|numeric|between:-180,180',
                'zoom_default' => 'nullable|integer|between:1,20',
            ], [
                'texto.required' => 'El texto de la pregunta es obligatorio.',
                'texto.max' => 'El texto de la pregunta no puede exceder 500 caracteres.',
                'texto.min' => 'El texto de la pregunta debe tener al menos 3 caracteres.',
                'tipo.required' => 'El tipo de pregunta es obligatorio.',
                'tipo.in' => 'El tipo de pregunta seleccionado no es válido.',
                'orden.required' => 'El orden es obligatorio.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
                'obligatoria.in' => 'El valor del campo obligatoria no es válido.',
                'min_caracteres.integer' => 'El mínimo de caracteres debe ser un número entero.',
                'min_caracteres.min' => 'El mínimo de caracteres no puede ser negativo.',
                'max_caracteres.integer' => 'El máximo de caracteres debe ser un número entero.',
                'max_caracteres.min' => 'El máximo de caracteres debe ser al menos 1.',
                'escala_min.integer' => 'El valor mínimo de la escala debe ser un número entero.',
                'escala_max.integer' => 'El valor máximo de la escala debe ser un número entero.',
                'escala_max.gt' => 'El valor máximo de la escala debe ser mayor al mínimo.',
                'escala_etiqueta_min.max' => 'La etiqueta mínima no puede exceder 100 caracteres.',
                'escala_etiqueta_max.max' => 'La etiqueta máxima no puede exceder 100 caracteres.',
                'tipos_archivo_permitidos.max' => 'Los tipos de archivo no pueden exceder 255 caracteres.',
                'tamano_max_archivo.integer' => 'El tamaño máximo debe ser un número entero.',
                'tamano_max_archivo.min' => 'El tamaño máximo debe ser al menos 1 MB.',
                'tamano_max_archivo.max' => 'El tamaño máximo no puede exceder 100 MB.',
                'latitud_default.numeric' => 'La latitud debe ser un número.',
                'latitud_default.between' => 'La latitud debe estar entre -90 y 90.',
                'longitud_default.numeric' => 'La longitud debe ser un número.',
                'longitud_default.between' => 'La longitud debe estar entre -180 y 180.',
                'zoom_default.integer' => 'El zoom debe ser un número entero.',
                'zoom_default.between' => 'El zoom debe estar entre 1 y 20.',
            ]);

            // Preparar datos para la pregunta
            $datosPregunta = [
                'encuesta_id' => $encuestaId,
                'texto' => $request->texto,
                'descripcion' => $request->descripcion,
                'placeholder' => $request->placeholder,
                'tipo' => $request->tipo,
                'obligatoria' => $request->has('obligatoria'),
                'min_caracteres' => $request->min_caracteres,
                'max_caracteres' => $request->max_caracteres,
                'escala_min' => $request->escala_min,
                'escala_max' => $request->escala_max,
                'escala_etiqueta_min' => $request->escala_etiqueta_min,
                'escala_etiqueta_max' => $request->escala_etiqueta_max,
                'tipos_archivo_permitidos' => $request->tipos_archivo_permitidos,
                'tamano_max_archivo' => $request->tamano_max_archivo,
                'latitud_default' => $request->latitud_default,
                'longitud_default' => $request->longitud_default,
                'zoom_default' => $request->zoom_default,
            ];

            // Calcular orden automáticamente si no se proporciona
            if (!$request->has('orden') || empty($request->orden)) {
                $datosPregunta['orden'] = Pregunta::calcularOrdenAutomatico($encuestaId);
            } else {
                $datosPregunta['orden'] = $request->orden;
            }

            // Log de datos preparados
            Log::info('Datos preparados para crear pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'datos' => $datosPregunta
            ]);

            // Verificar que el orden no esté duplicado
            $ordenExistente = Pregunta::where('encuesta_id', $encuestaId)
                ->where('orden', $datosPregunta['orden'])
                ->exists();

            if ($ordenExistente) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe una pregunta con ese orden. Por favor, elige otro orden.');
            }

            // Crear la pregunta
            $pregunta = Pregunta::create($datosPregunta);

            // Verificar que se creó correctamente
            if (!$pregunta->id) {
                throw new Exception('La pregunta no se creó correctamente - no se generó ID');
            }

            DB::commit();

            Log::info('Pregunta creada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $pregunta->id,
                'tipo' => $pregunta->tipo
            ]);

            // Verificar si puede avanzar a respuestas
            if ($encuesta->puedeAvanzarA('respuestas')) {
                return redirect()->route('encuestas.respuestas.create', $encuestaId)
                    ->with('success', 'Pregunta agregada correctamente. Ahora configura las respuestas.');
            } else {
                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('success', 'Pregunta agregada correctamente. Continúa agregando preguntas o configura las respuestas.');
            }
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creando pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al agregar la pregunta: ' . $e->getMessage());
        }
    }

    public function edit($encuestaId, $preguntaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.edit'])) {
                $this->logAccessDenied('preguntas.edit', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para editar preguntas.');
            }

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);
            $pregunta = Pregunta::findOrFail($preguntaId);

            // Verificar que la pregunta pertenece a la encuesta
            if ($pregunta->encuesta_id !== $encuesta->id) {
                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('error', 'La pregunta no pertenece a esta encuesta.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.edit', ['Superadmin', 'Admin'], ['preguntas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            Log::info('Acceso exitoso a editar pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId
            ]);

            return view('encuestas.preguntas.edit', compact('encuesta', 'pregunta'));
        } catch (Exception $e) {
            Log::error('Error accediendo a editar pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('error', 'Error al cargar la página de edición: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $encuestaId, $preguntaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.update'])) {
                $this->logAccessDenied('preguntas.update', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.update']);
                return $this->redirectIfNoAccess('No tienes permisos para actualizar preguntas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);
            $pregunta = Pregunta::findOrFail($preguntaId);

            // Verificar que la pregunta pertenece a la encuesta
            if ($pregunta->encuesta_id !== $encuesta->id) {
                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('error', 'La pregunta no pertenece a esta encuesta.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.update', ['Superadmin', 'Admin'], ['preguntas.update']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            $request->validate([
                'texto' => 'required|string|max:500|min:3',
                'descripcion' => 'nullable|string|max:1000',
                'placeholder' => 'nullable|string|max:255',
                'tipo' => 'required|in:respuesta_corta,parrafo,seleccion_unica,casillas_verificacion,lista_desplegable,escala_lineal,cuadricula_opcion_multiple,cuadricula_casillas,fecha,hora,carga_archivos,ubicacion_mapa,logica_condicional',
                'orden' => 'required|integer|min:1',
                'obligatoria' => 'nullable|in:on,1,true',
                'min_caracteres' => 'nullable|integer|min:0',
                'max_caracteres' => 'nullable|integer|min:1',
                'escala_min' => 'nullable|integer',
                'escala_max' => 'nullable|integer|gt:escala_min',
                'escala_etiqueta_min' => 'nullable|string|max:100',
                'escala_etiqueta_max' => 'nullable|string|max:100',
                'tipos_archivo_permitidos' => 'nullable|string|max:255',
                'tamano_max_archivo' => 'nullable|integer|min:1|max:100',
                'latitud_default' => 'nullable|numeric|between:-90,90',
                'longitud_default' => 'nullable|numeric|between:-180,180',
                'zoom_default' => 'nullable|integer|between:1,20',
            ], [
                'texto.required' => 'El texto de la pregunta es obligatorio.',
                'texto.max' => 'El texto de la pregunta no puede exceder 500 caracteres.',
                'texto.min' => 'El texto de la pregunta debe tener al menos 3 caracteres.',
                'tipo.required' => 'El tipo de pregunta es obligatorio.',
                'tipo.in' => 'El tipo de pregunta seleccionado no es válido.',
                'orden.required' => 'El orden es obligatorio.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
                'obligatoria.in' => 'El valor del campo obligatoria no es válido.',
                'min_caracteres.integer' => 'El mínimo de caracteres debe ser un número entero.',
                'min_caracteres.min' => 'El mínimo de caracteres no puede ser negativo.',
                'max_caracteres.integer' => 'El máximo de caracteres debe ser un número entero.',
                'max_caracteres.min' => 'El máximo de caracteres debe ser al menos 1.',
                'escala_min.integer' => 'El valor mínimo de la escala debe ser un número entero.',
                'escala_max.integer' => 'El valor máximo de la escala debe ser un número entero.',
                'escala_max.gt' => 'El valor máximo de la escala debe ser mayor al mínimo.',
                'escala_etiqueta_min.max' => 'La etiqueta mínima no puede exceder 100 caracteres.',
                'escala_etiqueta_max.max' => 'La etiqueta máxima no puede exceder 100 caracteres.',
                'tipos_archivo_permitidos.max' => 'Los tipos de archivo no pueden exceder 255 caracteres.',
                'tamano_max_archivo.integer' => 'El tamaño máximo debe ser un número entero.',
                'tamano_max_archivo.min' => 'El tamaño máximo debe ser al menos 1 MB.',
                'tamano_max_archivo.max' => 'El tamaño máximo no puede exceder 100 MB.',
                'latitud_default.numeric' => 'La latitud debe ser un número.',
                'latitud_default.between' => 'La latitud debe estar entre -90 y 90.',
                'longitud_default.numeric' => 'La longitud debe ser un número.',
                'longitud_default.between' => 'La longitud debe estar entre -180 y 180.',
                'zoom_default.integer' => 'El zoom debe ser un número entero.',
                'zoom_default.between' => 'El zoom debe estar entre 1 y 20.',
            ]);

            // Actualizar la pregunta
            $pregunta->update([
                'texto' => $request->texto,
                'descripcion' => $request->descripcion,
                'placeholder' => $request->placeholder,
                'tipo' => $request->tipo,
                'orden' => $request->orden,
                'obligatoria' => $request->has('obligatoria'),
                'min_caracteres' => $request->min_caracteres,
                'max_caracteres' => $request->max_caracteres,
                'escala_min' => $request->escala_min,
                'escala_max' => $request->escala_max,
                'escala_etiqueta_min' => $request->escala_etiqueta_min,
                'escala_etiqueta_max' => $request->escala_etiqueta_max,
                'tipos_archivo_permitidos' => $request->tipos_archivo_permitidos,
                'tamano_max_archivo' => $request->tamano_max_archivo,
                'latitud_default' => $request->latitud_default,
                'longitud_default' => $request->longitud_default,
                'zoom_default' => $request->zoom_default,
            ]);

            DB::commit();

            Log::info('Pregunta actualizada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId,
                'nuevo_tipo' => $request->tipo
            ]);

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('success', 'Pregunta actualizada exitosamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la pregunta: ' . $e->getMessage());
        }
    }

    public function destroyAll($encuestaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.destroy'])) {
                $this->logAccessDenied('preguntas.destroy', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar preguntas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.destroy', ['Superadmin', 'Admin'], ['preguntas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            $preguntasCount = $encuesta->preguntas->count();

            if ($preguntasCount === 0) {
                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('warning', 'No hay preguntas para eliminar.');
            }

            // Eliminar todas las preguntas (las respuestas se eliminan automáticamente por CASCADE)
            $encuesta->preguntas()->delete();

            DB::commit();

            Log::info('Todas las preguntas eliminadas exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'preguntas_eliminadas' => $preguntasCount
            ]);

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('success', "Se eliminaron {$preguntasCount} pregunta(s) exitosamente.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando todas las preguntas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('error', 'Error al eliminar las preguntas: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una pregunta
     */
    public function destroy($encuestaId, $preguntaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['preguntas.destroy'])) {
                $this->logAccessDenied('preguntas.destroy', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar preguntas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);
            $pregunta = Pregunta::where('encuesta_id', $encuestaId)
                ->where('id', $preguntaId)
                ->firstOrFail();

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.destroy', ['Superadmin', 'Admin'], ['preguntas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar esta pregunta.');
            }

            $pregunta->delete();

            DB::commit();

            Log::info('Pregunta eliminada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId
            ]);

            return redirect()->back()->with('success', 'Pregunta eliminada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error eliminando pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al eliminar la pregunta: ' . $e->getMessage());
        }
    }

    /**
     * Verificar acceso del usuario basado en roles y permisos
     */
    private function checkUserAccess(array $requiredPermissions = []): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Superadmin tiene acceso total
        if ($this->userHasRole('Superadmin')) {
            return true;
        }

        // Verificar permisos específicos
        if (!empty($requiredPermissions)) {
            foreach ($requiredPermissions as $permission) {
                if ($this->userHasPermission($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    private function userHasRole(string $role): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasRole($role);
        } catch (\Exception $e) {
            Log::error('Error verificando rol del usuario', [
                'user_id' => $user->id,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    private function userHasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasPermissionTo($permission);
        } catch (\Exception $e) {
            Log::error('Error verificando permiso del usuario', [
                'user_id' => $user->id,
                'permission' => $permission,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario es admin
     */
    private function isAdmin(): bool
    {
        return $this->userHasRole('Superadmin') || $this->userHasRole('Admin');
    }

    /**
     * Redirigir si no tiene acceso
     */
    private function redirectIfNoAccess(string $message): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('encuestas.index')->with('error', $message);
    }

    /**
     * Registrar intento de acceso denegado
     */
    private function logAccessDenied(string $action, array $requiredRoles = [], array $requiredPermissions = []): void
    {
        Log::warning('Acceso denegado a preguntas', [
            'user_id' => Auth::id(),
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions,
            'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'user_permissions' => Auth::user()->permissions->pluck('name')->toArray()
        ]);
    }
}
