<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Exception;

class PreguntaWizardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Paso 1: Selección de encuesta
     */
    public function index()
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['preguntas.create'])) {
                $this->logAccessDenied('preguntas.wizard', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para agregar preguntas.');
            }

            // Obtener encuestas disponibles para el usuario
            $encuestas = Encuesta::with(['empresa', 'preguntas'])
                ->when(!$this->isAdmin(), function($query) {
                    return $query->where('user_id', Auth::id());
                })
                ->where('estado', '!=', 'completada')
                ->orderBy('created_at', 'desc')
                ->get();

            // Inicializar contador de sesión si no existe
            if (!Session::has('wizard_preguntas_count')) {
                Session::put('wizard_preguntas_count', 0);
            }

            return view('preguntas.wizard.index', compact('encuestas'));
        } catch (Exception $e) {
            Log::error('Error en wizard de preguntas - index', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el wizard: ' . $e->getMessage());
        }
    }

    /**
     * Paso 2: Formulario de pregunta
     */
    public function create(Request $request)
    {
        try {
            // Obtener encuesta ID desde request o sesión
            $encuestaId = $request->get('encuesta_id') ?? Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('preguntas.wizard.index')
                    ->with('error', 'Debes seleccionar una encuesta.');
            }

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.wizard.create', ['Superadmin', 'Admin'], ['preguntas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            // Guardar encuesta seleccionada en sesión si no existe
            if (!Session::has('wizard_encuesta_id')) {
                Session::put('wizard_encuesta_id', $encuestaId);
            }

            Log::info('Acceso al wizard de preguntas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'preguntas_existentes' => $encuesta->preguntas->count(),
                'preguntas_en_sesion' => Session::get('wizard_preguntas_count', 0)
            ]);

            return view('preguntas.wizard.create', compact('encuesta'));
        } catch (Exception $e) {
            Log::error('Error en wizard de preguntas - create', [
                'user_id' => Auth::id(),
                'encuesta_id' => $request->get('encuesta_id'),
                'error' => $e->getMessage()
            ]);
            return redirect()->route('preguntas.wizard.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Paso 3: Guardar pregunta y preguntar si continuar
     */
    public function store(Request $request)
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('preguntas.wizard.index')
                    ->with('error', 'Sesión de wizard expirada. Selecciona una encuesta nuevamente.');
            }

            // Verificar permisos
            if (!$this->checkUserAccess(['preguntas.store'])) {
                $this->logAccessDenied('preguntas.wizard.store', ['Superadmin', 'Admin', 'Cliente'], ['preguntas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para guardar preguntas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('preguntas.wizard.store', ['Superadmin', 'Admin'], ['preguntas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            // Validación
            $request->validate([
                'texto' => 'required|string|max:500|min:3',
                'descripcion' => 'nullable|string|max:1000',
                'placeholder' => 'nullable|string|max:255',
                'tipo' => 'required|string',
                'orden' => 'nullable|integer|min:1',
                'obligatoria' => 'nullable',
                'min_caracteres' => 'nullable|integer|min:0',
                'max_caracteres' => 'nullable|integer|min:1',
                'escala_min' => 'nullable|integer',
                'escala_max' => 'nullable|integer',
                'escala_etiqueta_min' => 'nullable|string|max:100',
                'escala_etiqueta_max' => 'nullable|string|max:100',
                'tipos_archivo_permitidos' => 'nullable|string|max:255',
                'tamano_max_archivo' => 'nullable|integer|min:1|max:100',
                'latitud_default' => 'nullable|numeric|between:-90,90',
                'longitud_default' => 'nullable|numeric|between:-180,180',
                'zoom_default' => 'nullable|integer|between:1,20',
            ], [
                'texto.required' => 'El texto de la pregunta es obligatorio.',
                'texto.min' => 'La pregunta debe tener al menos 3 caracteres.',
                'texto.max' => 'La pregunta no puede exceder 500 caracteres.',
                'tipo.required' => 'Debes seleccionar un tipo de pregunta.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
                'min_caracteres.integer' => 'El mínimo de caracteres debe ser un número entero.',
                'min_caracteres.min' => 'El mínimo de caracteres no puede ser negativo.',
                'max_caracteres.integer' => 'El máximo de caracteres debe ser un número entero.',
                'max_caracteres.min' => 'El máximo de caracteres debe ser mayor a 0.',
                'escala_min.integer' => 'La escala mínima debe ser un número entero.',
                'escala_max.integer' => 'La escala máxima debe ser un número entero.',
                'escala_etiqueta_min.max' => 'La etiqueta mínima no puede exceder 100 caracteres.',
                'escala_etiqueta_max.max' => 'La etiqueta máxima no puede exceder 100 caracteres.',
                'tipos_archivo_permitidos.max' => 'Los tipos de archivo no pueden exceder 255 caracteres.',
                'tamano_max_archivo.integer' => 'El tamaño máximo debe ser un número entero.',
                'tamano_max_archivo.min' => 'El tamaño máximo debe ser mayor a 0.',
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

            // Incrementar contador de sesión
            $preguntasCount = Session::get('wizard_preguntas_count', 0) + 1;
            Session::put('wizard_preguntas_count', $preguntasCount);

            // Recargar la encuesta para obtener el conteo actualizado
            $encuesta->refresh();

            DB::commit();

            Log::info('Pregunta creada en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $pregunta->id,
                'tipo' => $pregunta->tipo,
                'preguntas_en_sesion' => $preguntasCount
            ]);

            return view('preguntas.wizard.confirm', compact('encuesta', 'pregunta', 'preguntasCount'));

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creando pregunta en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => Session::get('wizard_encuesta_id'),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al agregar la pregunta: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar si continuar agregando preguntas
     */
    public function confirm(Request $request)
    {
        try {
            $action = $request->get('action');
            $encuestaId = Session::get('wizard_encuesta_id');
            $preguntasCount = Session::get('wizard_preguntas_count', 0);

            if (!$encuestaId) {
                return redirect()->route('preguntas.wizard.index')
                    ->with('error', 'Sesión de wizard expirada. Selecciona una encuesta nuevamente.');
            }

            if ($action === 'continue') {
                // Continuar agregando preguntas - mantener la sesión activa
                Log::info('Continuando wizard de preguntas', [
                    'user_id' => Auth::id(),
                    'encuesta_id' => $encuestaId,
                    'preguntas_en_sesion' => $preguntasCount
                ]);

                return redirect()->route('preguntas.wizard.create')
                    ->with('success', 'Pregunta agregada correctamente. Puedes agregar otra pregunta.');
            } else {
                // Finalizar wizard
                $encuesta = Encuesta::findOrFail($encuestaId);

                // Limpiar sesión
                Session::forget(['wizard_encuesta_id', 'wizard_preguntas_count']);

                Log::info('Wizard de preguntas finalizado', [
                    'user_id' => Auth::id(),
                    'encuesta_id' => $encuestaId,
                    'preguntas_creadas' => $preguntasCount
                ]);

                // Crear respuesta con cookies expiradas para limpiar el estado
                $response = redirect()->route('encuestas.show', $encuestaId)
                    ->with('success', "Wizard completado. Se agregaron {$preguntasCount} pregunta(s) a la encuesta.");

                // Limpiar cookies del wizard
                $response->cookie('wizard_encuesta_id', '', -1);
                $response->cookie('wizard_preguntas_count', '', -1);

                return $response;
            }
        } catch (Exception $e) {
            Log::error('Error en confirmación del wizard', [
                'user_id' => Auth::id(),
                'action' => $request->get('action'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('preguntas.wizard.index')
                ->with('error', 'Error en la confirmación: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar wizard
     */
    public function cancel()
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');
            $preguntasCount = Session::get('wizard_preguntas_count', 0);

            // Limpiar sesión
            Session::forget(['wizard_encuesta_id', 'wizard_preguntas_count']);

            Log::info('Wizard de preguntas cancelado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'preguntas_en_sesion' => $preguntasCount
            ]);

            // Crear respuesta con cookies expiradas para limpiar el estado
            $response = redirect()->route('preguntas.wizard.index')
                ->with('info', 'Wizard cancelado. No se guardaron cambios.');

            // Limpiar cookies del wizard
            $response->cookie('wizard_encuesta_id', '', -1);
            $response->cookie('wizard_preguntas_count', '', -1);

            return $response;

        } catch (Exception $e) {
            Log::error('Error cancelando wizard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('preguntas.wizard.index')
                ->with('error', 'Error al cancelar: ' . $e->getMessage());
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
        return $this->userHasAnyRole(['Superadmin', 'Admin']);
    }

    /**
     * Verificar si el usuario tiene al menos uno de los roles especificados
     */
    private function userHasAnyRole(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasAnyRole($roles);
        } catch (\Exception $e) {
            Log::error('Error verificando roles del usuario', [
                'user_id' => $user->id,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Redirigir si no tiene acceso
     */
    private function redirectIfNoAccess(string $message)
    {
        Log::warning('Acceso denegado', [
            'user_id' => Auth::id(),
            'message' => $message
        ]);

        return redirect()->route('encuestas.index')->with('error', $message);
    }

    /**
     * Log de acceso denegado
     */
    private function logAccessDenied(string $action, array $requiredRoles, array $requiredPermissions)
    {
        Log::warning('Acceso denegado', [
            'user_id' => Auth::id(),
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions
        ]);
    }
}
