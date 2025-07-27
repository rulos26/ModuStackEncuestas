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
                'tipo' => 'required|in:texto,seleccion_unica,seleccion_multiple,numero,fecha',
                'orden' => 'required|integer|min:1',
                'obligatoria' => 'boolean',
            ], [
                'texto.required' => 'El texto de la pregunta es obligatorio.',
                'texto.max' => 'El texto de la pregunta no puede exceder 500 caracteres.',
                'texto.min' => 'El texto de la pregunta debe tener al menos 3 caracteres.',
                'tipo.required' => 'El tipo de pregunta es obligatorio.',
                'tipo.in' => 'El tipo de pregunta seleccionado no es válido.',
                'orden.required' => 'El orden es obligatorio.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
            ]);

            // Verificar que el orden no esté duplicado
            $ordenExistente = Pregunta::where('encuesta_id', $encuestaId)
                ->where('orden', $request->orden)
                ->exists();

            if ($ordenExistente) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe una pregunta con ese orden. Por favor, elige otro orden.');
            }

            // Crear la pregunta
            $pregunta = Pregunta::create([
                'encuesta_id' => $encuestaId,
                'texto' => $request->texto,
                'tipo' => $request->tipo,
                'orden' => $request->orden,
                'obligatoria' => $request->has('obligatoria'),
            ]);

            DB::commit();

            Log::info('Pregunta creada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $pregunta->id,
                'tipo' => $pregunta->tipo
            ]);

            return redirect()->back()->with('success', 'Pregunta agregada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creando pregunta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al agregar la pregunta: ' . $e->getMessage());
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
