<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empresa;
use App\Http\Requests\EncuestaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.index'])) {
                $this->logAccessDenied('encuestas.index', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.index']);
                return $this->redirectIfNoAccess('No tienes permisos para ver encuestas.');
            }

            $encuestas = Encuesta::with(['empresa', 'user'])
                ->orderByDesc('created_at')
                ->paginate(10);

            return view('encuestas.index', compact('encuestas'));
        } catch (Exception $e) {
            Log::error('Error en index de encuestas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar las encuestas: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.show'])) {
                $this->logAccessDenied('encuestas.show', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.show']);
                return $this->redirectIfNoAccess('No tienes permisos para ver encuestas.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa', 'user'])->findOrFail($id);

            return view('encuestas.show', compact('encuesta'));
        } catch (Exception $e) {
            Log::error('Error mostrando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('encuestas.index')->with('error', 'Encuesta no encontrada.');
        }
    }

    public function create()
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.create'])) {
                $this->logAccessDenied('encuestas.create', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para crear encuestas.');
            }

            $empresas = Empresa::orderBy('nombre')->get();

            if ($empresas->isEmpty()) {
                return redirect()->back()->with('warning', 'Debes crear una empresa antes de crear encuestas.');
            }

            return view('encuestas.create', compact('empresas'));
        } catch (Exception $e) {
            Log::error('Error en create de encuestas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function store(EncuestaRequest $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.create'])) {
                $this->logAccessDenied('encuestas.store', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.create']);
                return $this->redirectIfNoAccess('No tienes permisos para crear encuestas.');
            }

            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['habilitada'] = $request->has('habilitada');

            $encuesta = Encuesta::create($data);

            DB::commit();

            Log::info('Encuesta creada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo
            ]);

            return redirect()->route('encuestas.show', $encuesta)
                ->with('success', 'Encuesta creada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creando encuesta', [
                'user_id' => Auth::id(),
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la encuesta: ' . $e->getMessage());
        }
    }

    public function edit(Encuesta $encuesta)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.edit'])) {
                $this->logAccessDenied('encuestas.edit', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para editar encuestas.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('encuestas.edit', ['Superadmin', 'Admin'], ['encuestas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para editar esta encuesta.');
            }

            $empresas = Empresa::orderBy('nombre')->get();

            return view('encuestas.edit', compact('encuesta', 'empresas'));
        } catch (Exception $e) {
            Log::error('Error editando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('encuestas.index')->with('error', 'Error al cargar la encuesta.');
        }
    }

    public function update(EncuestaRequest $request, Encuesta $encuesta)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.edit'])) {
                $this->logAccessDenied('encuestas.update', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para editar encuestas.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('encuestas.update', ['Superadmin', 'Admin'], ['encuestas.edit']);
                return $this->redirectIfNoAccess('No tienes permisos para editar esta encuesta.');
            }

            DB::beginTransaction();

            $data = $request->validated();
            $data['habilitada'] = $request->has('habilitada');

            $encuesta->update($data);

            DB::commit();

            Log::info('Encuesta actualizada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo
            ]);

            return redirect()->route('encuestas.show', $encuesta)
                ->with('success', 'Encuesta actualizada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la encuesta: ' . $e->getMessage());
        }
    }

    public function destroy(Encuesta $encuesta)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.destroy'])) {
                $this->logAccessDenied('encuestas.destroy', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar encuestas.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('encuestas.destroy', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar esta encuesta.');
            }

            // Verificar que no tenga preguntas asociadas
            if ($encuesta->preguntas()->count() > 0) {
                return redirect()->back()->with('error', 'No se puede eliminar una encuesta que tiene preguntas asociadas.');
            }

            $encuesta->delete();

            Log::info('Encuesta eliminada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo
            ]);

            return redirect()->route('encuestas.index')
                ->with('success', 'Encuesta eliminada correctamente.');
        } catch (Exception $e) {
            Log::error('Error eliminando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
        }
    }

    public function clonar($id)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.clone'])) {
                $this->logAccessDenied('encuestas.clone', ['Superadmin', 'Admin', 'Cliente'], ['encuestas.clone']);
                return $this->redirectIfNoAccess('No tienes permisos para clonar encuestas.');
            }

            DB::beginTransaction();

            $encuestaOriginal = Encuesta::with(['preguntas.respuestas'])->findOrFail($id);

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuestaOriginal->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('encuestas.clone', ['Superadmin', 'Admin'], ['encuestas.clone']);
                return $this->redirectIfNoAccess('No tienes permisos para clonar esta encuesta.');
            }

            // Clonar encuesta
            $encuestaClonada = $encuestaOriginal->replicate();
            $encuestaClonada->titulo = $encuestaOriginal->titulo . ' (Copia)';
            $encuestaClonada->estado = 'borrador';
            $encuestaClonada->habilitada = false;
            $encuestaClonada->user_id = Auth::id();
            $encuestaClonada->save();

            // Clonar preguntas y respuestas
            foreach ($encuestaOriginal->preguntas as $pregunta) {
                $preguntaClonada = $pregunta->replicate();
                $preguntaClonada->encuesta_id = $encuestaClonada->id;
                $preguntaClonada->save();

                foreach ($pregunta->respuestas as $respuesta) {
                    $respuestaClonada = $respuesta->replicate();
                    $respuestaClonada->pregunta_id = $preguntaClonada->id;
                    $respuestaClonada->save();
                }
            }

            DB::commit();

            Log::info('Encuesta clonada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_original_id' => $id,
                'encuesta_clonada_id' => $encuestaClonada->id
            ]);

            return redirect()->route('encuestas.show', $encuestaClonada)
                ->with('success', 'Encuesta clonada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error clonando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al clonar la encuesta: ' . $e->getMessage());
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
    private function redirectIfNoAccess(string $message): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('home')->with('error', $message);
    }

    /**
     * Log de acceso denegado
     */
    private function logAccessDenied(string $action, array $requiredRoles = [], array $requiredPermissions = []): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        Log::warning('Acceso denegado a encuestas', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ]);
    }
}
