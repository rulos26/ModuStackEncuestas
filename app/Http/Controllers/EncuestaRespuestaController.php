<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaRespuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['respuestas.create'])) {
                $this->logAccessDenied('respuestas.create', ['Superadmin', 'Admin', 'Cliente'], ['respuestas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para agregar respuestas.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas'])->findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('respuestas.create', ['Superadmin', 'Admin'], ['respuestas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            // Obtener preguntas de selección con sus respuestas
            $preguntas = $encuesta->preguntas()
                ->necesitaRespuestas()
                ->with('respuestas')
                ->get();

            if ($preguntas->isEmpty()) {
                Log::warning('No hay preguntas que necesiten respuestas', [
                    'user_id' => Auth::id(),
                    'encuesta_id' => $encuestaId,
                    'total_preguntas' => $encuesta->preguntas->count()
                ]);

                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('warning', 'No hay preguntas de selección que requieran respuestas predefinidas.')
                    ->with('show_add_questions_modal', true);
            }

            // CALCULAR ESTADÍSTICAS DEL DASHBOARD
            $totalPreguntas = $encuesta->preguntas->count();
            $preguntasConRespuestas = $preguntas->filter(function($pregunta) {
                return $pregunta->respuestas->isNotEmpty();
            });
            $preguntasSinRespuestas = $preguntas->filter(function($pregunta) {
                return $pregunta->respuestas->isEmpty();
            });

            // Verificar si todas las preguntas tienen respuestas para activar lógica
            $todasTienenRespuestas = $preguntasSinRespuestas->isEmpty();
            $puedeConfigurarLogica = $todasTienenRespuestas && $preguntas->isNotEmpty();

            Log::info('Acceso exitoso a agregar respuestas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'preguntas_total' => $preguntas->count(),
                'preguntas_sin_respuestas' => $preguntasSinRespuestas->count(),
                'preguntas_con_respuestas' => $preguntasConRespuestas->count(),
                'puede_configurar_logica' => $puedeConfigurarLogica
            ]);

            return view('encuestas.respuestas.create', compact(
                'preguntas',
                'preguntasSinRespuestas',
                'preguntasConRespuestas',
                'encuestaId',
                'encuesta',
                'totalPreguntas',
                'puedeConfigurarLogica'
            ));
        } catch (Exception $e) {
            Log::error('Error accediendo a agregar respuestas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.index')
                ->with('error', 'Error al cargar la página de respuestas: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $encuestaId)
    {
        try {
            // Verificar permisos de acceso
            if (!$this->checkUserAccess(['respuestas.store'])) {
                $this->logAccessDenied('respuestas.store', ['Superadmin', 'Admin', 'Cliente'], ['respuestas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para guardar respuestas.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('respuestas.store', ['Superadmin', 'Admin'], ['respuestas.store']);
                return $this->redirectIfNoAccess('No tienes permisos para modificar esta encuesta.');
            }

            // Validar que se enviaron respuestas
            if (empty($request->respuestas)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe agregar al menos una respuesta.');
            }

            // Aplanar la estructura de respuestas
            $respuestasAplanadas = [];
            foreach ($request->respuestas as $preguntaId => $respuestasPregunta) {
                foreach ($respuestasPregunta as $respuesta) {
                    if (!empty($respuesta['texto'])) {
                        $respuestasAplanadas[] = [
                            'pregunta_id' => $preguntaId,
                            'texto' => trim($respuesta['texto']),
                            'orden' => $respuesta['orden'] ?? 1,
                        ];
                    }
                }
            }

            if (empty($respuestasAplanadas)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe agregar al menos una respuesta válida.');
            }

            // Validar datos
            foreach ($respuestasAplanadas as $respuesta) {
                if (strlen($respuesta['texto']) < 1 || strlen($respuesta['texto']) > 255) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El texto de la respuesta debe tener entre 1 y 255 caracteres.');
                }

                if ($respuesta['orden'] < 1) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El orden debe ser mayor a 0.');
                }
            }

            // Verificar que las preguntas pertenecen a la encuesta
            $preguntasEncuesta = $encuesta->preguntas()->pluck('id')->toArray();
            $preguntasEnRespuestas = array_unique(array_column($respuestasAplanadas, 'pregunta_id'));

            foreach ($preguntasEnRespuestas as $preguntaId) {
                if (!in_array($preguntaId, $preguntasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Una de las preguntas no pertenece a esta encuesta.');
                }
            }

            // Eliminar respuestas existentes para las preguntas que se van a actualizar
            Respuesta::whereIn('pregunta_id', $preguntasEnRespuestas)->delete();

            // Crear nuevas respuestas
            foreach ($respuestasAplanadas as $data) {
                Respuesta::create([
                    'pregunta_id' => $data['pregunta_id'],
                    'texto' => $data['texto'],
                    'orden' => $data['orden'],
                ]);
            }

            DB::commit();

            Log::info('Respuestas guardadas exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'respuestas_count' => count($respuestasAplanadas)
            ]);

            // Verificar si puede avanzar a lógica
            if ($encuesta->puedeAvanzarA('logica')) {
                return redirect()->route('encuestas.logica.create', $encuestaId)
                    ->with('success', 'Respuestas guardadas correctamente. Ahora configura la lógica de la encuesta.');
            } else {
                return redirect()->route('encuestas.respuestas.create', $encuestaId)
                    ->with('success', 'Respuestas guardadas correctamente. Completa todas las respuestas para continuar.');
            }
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error guardando respuestas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar las respuestas: ' . $e->getMessage());
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
        Log::warning('Acceso denegado a respuestas', [
            'user_id' => Auth::id(),
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions,
            'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'user_permissions' => Auth::user()->permissions->pluck('name')->toArray()
        ]);
    }

    /**
     * Obtener respuestas de una pregunta específica
     */
    public function obtenerRespuestas($preguntaId)
    {
        try {
            $pregunta = Pregunta::with('respuestas')->findOrFail($preguntaId);

            // Verificar permisos
            if (!$this->checkUserAccess(['respuestas.create'])) {
                return response()->json(['error' => 'No tienes permisos para acceder a las respuestas.'], 403);
            }

            // Verificar que el usuario es el propietario de la encuesta
            $encuesta = $pregunta->encuesta;
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                return response()->json(['error' => 'No tienes permisos para modificar esta encuesta.'], 403);
            }

            $respuestas = $pregunta->respuestas->map(function($respuesta) {
                return [
                    'id' => $respuesta->id,
                    'texto' => $respuesta->texto,
                    'orden' => $respuesta->orden
                ];
            });

            return response()->json([
                'success' => true,
                'respuestas' => $respuestas
            ]);

        } catch (Exception $e) {
            Log::error('Error obteniendo respuestas', [
                'user_id' => Auth::id(),
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Error al obtener las respuestas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Editar respuestas de una pregunta específica
     */
    public function editarRespuestas(Request $request, $preguntaId)
    {
        try {
            $pregunta = Pregunta::findOrFail($preguntaId);

            // Verificar permisos
            if (!$this->checkUserAccess(['respuestas.create'])) {
                return response()->json(['error' => 'No tienes permisos para editar respuestas.'], 403);
            }

            // Verificar que el usuario es el propietario de la encuesta
            $encuesta = $pregunta->encuesta;
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                return response()->json(['error' => 'No tienes permisos para modificar esta encuesta.'], 403);
            }

                        DB::beginTransaction();

            try {
                $respuestas = $request->input('respuestas', []);

                Log::info('Iniciando edición de respuestas', [
                    'user_id' => Auth::id(),
                    'pregunta_id' => $preguntaId,
                    'total_respuestas_recibidas' => count($respuestas),
                    'datos_recibidos' => $respuestas
                ]);

                $respuestasActualizadas = 0;
                $respuestasCreadas = 0;
                $respuestasEliminadas = 0;

                // Actualizar respuestas existentes y crear nuevas
                foreach ($respuestas as $index => $respuestaData) {
                    if (isset($respuestaData['id']) && !empty($respuestaData['id'])) {
                        // Actualizar respuesta existente
                        $respuesta = Respuesta::find($respuestaData['id']);
                        if ($respuesta && $respuesta->pregunta_id == $preguntaId) {
                            $respuesta->update([
                                'texto' => $respuestaData['texto'],
                                'orden' => $respuestaData['orden'] ?? $index + 1
                            ]);
                            $respuestasActualizadas++;

                            Log::info('Respuesta actualizada', [
                                'respuesta_id' => $respuesta->id,
                                'texto_anterior' => $respuesta->getOriginal('texto'),
                                'texto_nuevo' => $respuestaData['texto']
                            ]);
                        }
                    } else {
                        // Crear nueva respuesta
                        $nuevaRespuesta = Respuesta::create([
                            'pregunta_id' => $preguntaId,
                            'texto' => $respuestaData['texto'],
                            'orden' => $respuestaData['orden'] ?? $index + 1
                        ]);
                        $respuestasCreadas++;

                        Log::info('Nueva respuesta creada', [
                            'respuesta_id' => $nuevaRespuesta->id,
                            'texto' => $respuestaData['texto']
                        ]);
                    }
                }

                // Eliminar respuestas que ya no están en la lista
                $respuestasIds = collect($respuestas)->pluck('id')->filter();
                $respuestasEliminadas = Respuesta::where('pregunta_id', $preguntaId)
                    ->whereNotIn('id', $respuestasIds)
                    ->delete();

                DB::commit();

                Log::info('Edición de respuestas completada', [
                    'user_id' => Auth::id(),
                    'pregunta_id' => $preguntaId,
                    'respuestas_actualizadas' => $respuestasActualizadas,
                    'respuestas_creadas' => $respuestasCreadas,
                    'respuestas_eliminadas' => $respuestasEliminadas
                ]);

                Log::info('Respuestas editadas exitosamente', [
                    'user_id' => Auth::id(),
                    'pregunta_id' => $preguntaId,
                    'total_respuestas' => count($respuestas)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Respuestas actualizadas exitosamente'
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error editando respuestas', [
                'user_id' => Auth::id(),
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Error al editar las respuestas: ' . $e->getMessage()], 500);
        }
    }
}
