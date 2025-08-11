<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\EmpresasCliente;
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
                ->get();

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

            $empresas = EmpresasCliente::orderBy('nombre')->get();
            //dd($empresas);
            if ($empresas->isEmpty()) {
                return redirect()->back()->with('warning', 'Debes crear una empresa cliente antes de crear encuestas.');
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

            // Log de inicio
            Log::info('Iniciando creación de encuesta', [
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            DB::beginTransaction();

            // Preparar datos
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['habilitada'] = $request->has('habilitada');

            // Asignar valor por defecto para numero_encuestas si no se proporciona
            if (!isset($data['numero_encuestas']) || empty($data['numero_encuestas'])) {
                $data['numero_encuestas'] = 100; // Valor por defecto: 100 encuestas
            }

            // Estado se maneja automáticamente en el modelo (por defecto: 'borrador')
            // $data['estado'] = $data['estado'] ?? 'borrador';

            // Log de datos preparados
            Log::info('Datos preparados para crear encuesta', [
                'user_id' => Auth::id(),
                'data' => $data
            ]);

            // Crear encuesta
            $encuesta = Encuesta::create($data);

            // Verificar que se creó correctamente
            if (!$encuesta->id) {
                throw new Exception('La encuesta no se creó correctamente - no se generó ID');
            }

            DB::commit();

            Log::info('Encuesta creada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'estado' => $encuesta->estado
            ]);

            // REDIRECCIÓN AUTOMÁTICA A AGREGAR PREGUNTAS
            return redirect()->route('encuestas.preguntas.create', $encuesta->id)
                ->with('success', 'Encuesta creada correctamente. Ahora agrega las preguntas.');
        } catch (Exception $e) {
            DB::rollBack();

            // Verificar si es error de empresa no encontrada
            if (str_contains($e->getMessage(), 'empresa_id_foreign') || str_contains($e->getMessage(), 'Integrity constraint violation')) {
                // Obtener empresas disponibles para mostrar en el error
                $empresasDisponibles = EmpresasCliente::orderBy('nombre')->get(['id', 'nombre']);

                Log::error('Error creando encuesta - Empresa no encontrada', [
                    'user_id' => Auth::id(),
                    'empresa_id_solicitada' => $request->input('empresa_id'),
                    'empresas_disponibles' => $empresasDisponibles->pluck('id', 'nombre')->toArray(),
                    'error' => $e->getMessage()
                ]);

                $mensajeError = '❌ Error: La empresa seleccionada no existe en la base de datos.<br><br>';
                $mensajeError .= '🏢 <strong>Empresas disponibles:</strong><br>';

                foreach ($empresasDisponibles as $empresa) {
                    $mensajeError .= "   • ID {$empresa->id}: {$empresa->nombre}<br>";
                }

                $mensajeError .= '<br>💡 <strong>Solución:</strong> Selecciona una empresa de la lista anterior.';

                return redirect()->back()
                    ->withInput()
                    ->with('error', $mensajeError);
            }

            Log::error('Error creando encuesta', [
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'validated_data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

            $empresas = EmpresasCliente::orderBy('nombre')->get();

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
            $data['enviar_por_correo'] = $request->has('enviar_por_correo');
            $data['envio_masivo_activado'] = $request->has('envio_masivo_activado');

            // Estado se maneja automáticamente en el backend
            // No se permite cambio manual desde el formulario
            // $nuevoEstado = $request->input('estado');
            // if ($nuevoEstado !== $encuesta->estado && in_array($nuevoEstado, ['enviada', 'publicada'])) {
            //     if (!$encuesta->puedeCambiarEstado($nuevoEstado)) {
            //         $errores = $encuesta->validarIntegridad();
            //         return redirect()->back()
            //             ->withInput()
            //             ->with('error', 'No se puede cambiar el estado. Errores de validación: ' . implode(', ', $errores));
            //     }

            //     // Marcar como validada
            //     $data['validacion_completada'] = true;
            //     $data['errores_validacion'] = null;
            // }

            $encuesta->update($data);

            DB::commit();

            Log::info('Encuesta actualizada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'estado_actual' => $encuesta->estado
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

            // Crear backup antes de borrar (opcional)
            $this->crearBackupEncuesta($encuesta);

            // Obtener estadísticas antes de borrar para el log
            $estadisticas = $this->obtenerEstadisticasEncuesta($encuesta);

            // Borrar la encuesta (cascade automático)
            $encuesta->delete();

            Log::info('Encuesta eliminada exitosamente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'estadisticas_eliminadas' => $estadisticas
            ]);

            return redirect()->route('encuestas.index')
                ->with('success', 'Encuesta eliminada correctamente junto con todos sus datos relacionados.');
        } catch (Exception $e) {
            Log::error('Error eliminando encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vista de confirmación de eliminación
     */
    public function confirmarEliminacion(Encuesta $encuesta)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.destroy'])) {
                $this->logAccessDenied('encuestas.confirmar-eliminacion', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar encuestas.');
            }

            // Verificar que el usuario es el propietario o tiene permisos de admin
            if ($encuesta->user_id !== Auth::id() && !$this->isAdmin()) {
                $this->logAccessDenied('encuestas.confirmar-eliminacion', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar esta encuesta.');
            }

            // Cargar relaciones para mostrar información
            $encuesta->load(['preguntas.respuestas', 'empresa', 'user', 'bloquesEnvio', 'tokensAcceso', 'configuracionesEnvio']);

            // Obtener estadísticas
            $estadisticas = $this->obtenerEstadisticasEncuesta($encuesta);

            return view('encuestas.confirmar-eliminacion', compact('encuesta', 'estadisticas'));
        } catch (Exception $e) {
            Log::error('Error mostrando confirmación de eliminación', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar la confirmación: ' . $e->getMessage());
        }
    }

    /**
     * Crear backup de la encuesta antes de eliminar
     */
    private function crearBackupEncuesta(Encuesta $encuesta)
    {
        try {
            // Cargar todas las relaciones
            $encuesta->load(['preguntas.respuestas', 'empresa', 'user', 'bloquesEnvio', 'tokensAcceso', 'configuracionesEnvio']);

            $backup = [
                'encuesta' => $encuesta->toArray(),
                'fecha_backup' => now()->toDateTimeString(),
                'usuario_backup' => Auth::id(),
                'accion' => 'eliminacion'
            ];

            // Guardar backup en logs (opcional: también se puede guardar en archivo o base de datos)
            Log::info('Backup de encuesta antes de eliminar', [
                'encuesta_id' => $encuesta->id,
                'backup' => $backup
            ]);

        } catch (Exception $e) {
            Log::warning('Error creando backup de encuesta', [
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas de la encuesta
     */
    private function obtenerEstadisticasEncuesta(Encuesta $encuesta)
    {
        return [
            'preguntas_count' => $encuesta->preguntas()->count(),
            'respuestas_count' => $encuesta->preguntas()->withCount('respuestas')->get()->sum('respuestas_count'),
            'respuestas_usuarios_count' => $encuesta->respuestasUsuarios()->count(),
            'bloques_envio_count' => $encuesta->bloquesEnvio()->count(),
            'tokens_acceso_count' => $encuesta->tokensAcceso()->count(),
            'configuraciones_envio_count' => $encuesta->configuracionesEnvio()->count(),
            'correos_enviados_count' => $encuesta->correosEnviados()->count(),
        ];
    }

    /**
     * Mostrar vista de eliminación masiva
     */
    public function eliminacionMasiva()
    {
        try {
            Log::info('🔍 Accediendo a eliminación masiva', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'N/A',
                'user_roles' => Auth::user()->roles->pluck('name') ?? [],
                'user_permissions' => Auth::user()->permissions->pluck('name') ?? []
            ]);

                        // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.destroy'])) {
                Log::warning('❌ Acceso denegado a eliminación masiva', [
                    'user_id' => Auth::id(),
                    'required_permissions' => ['encuestas.destroy']
                ]);
                $this->logAccessDenied('encuestas.eliminacion-masiva', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar encuestas.');
            }

            // Obtener encuestas disponibles para el usuario
            $encuestas = Encuesta::with(['empresa', 'user', 'preguntas'])
                ->when(!$this->isAdmin(), function($query) {
                    return $query->where('user_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('✅ Eliminación masiva cargada exitosamente', [
                'user_id' => Auth::id(),
                'encuestas_count' => $encuestas->count(),
                'is_admin' => $this->isAdmin()
            ]);

            return view('encuestas.eliminacion-masiva', compact('encuestas'));
        } catch (Exception $e) {
            Log::error('Error mostrando eliminación masiva', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar la eliminación masiva: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar eliminación masiva
     */
    public function confirmarEliminacionMasiva(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.destroy'])) {
                $this->logAccessDenied('encuestas.confirmar-eliminacion-masiva', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar encuestas.');
            }

            $encuestaIds = $request->input('encuesta_ids', []);

            if (empty($encuestaIds)) {
                return redirect()->back()->with('error', 'Debes seleccionar al menos una encuesta para eliminar.');
            }

            // Obtener encuestas seleccionadas
            $encuestas = Encuesta::with([
                'preguntas.respuestas',
                'empresa',
                'user',
                'bloquesEnvio',
                'tokensAcceso',
                'configuracionesEnvio',
                'correosEnviados',
                'respuestasUsuarios'
            ])
            ->whereIn('id', $encuestaIds)
            ->when(!$this->isAdmin(), function($query) {
                return $query->where('user_id', Auth::id());
            })
            ->get();

            if ($encuestas->isEmpty()) {
                return redirect()->back()->with('error', 'No se encontraron encuestas válidas para eliminar.');
            }

            // Calcular estadísticas totales
            $estadisticasTotales = $this->calcularEstadisticasMasivas($encuestas);

            return view('encuestas.confirmar-eliminacion-masiva', compact('encuestas', 'estadisticasTotales'));
        } catch (Exception $e) {
            Log::error('Error confirmando eliminación masiva', [
                'user_id' => Auth::id(),
                'encuesta_ids' => $request->input('encuesta_ids'),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al confirmar eliminación masiva: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar eliminación masiva
     */
    public function ejecutarEliminacionMasiva(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['encuestas.destroy'])) {
                $this->logAccessDenied('encuestas.ejecutar-eliminacion-masiva', ['Superadmin', 'Admin'], ['encuestas.destroy']);
                return $this->redirectIfNoAccess('No tienes permisos para eliminar encuestas.');
            }

            $encuestaIds = $request->input('encuesta_ids', []);

            if (empty($encuestaIds)) {
                return redirect()->back()->with('error', 'No se especificaron encuestas para eliminar.');
            }

            // Obtener encuestas para backup
            $encuestas = Encuesta::with([
                'preguntas.respuestas',
                'empresa',
                'user',
                'bloquesEnvio',
                'tokensAcceso',
                'configuracionesEnvio',
                'correosEnviados',
                'respuestasUsuarios'
            ])
            ->whereIn('id', $encuestaIds)
            ->when(!$this->isAdmin(), function($query) {
                return $query->where('user_id', Auth::id());
            })
            ->get();

            if ($encuestas->isEmpty()) {
                return redirect()->back()->with('error', 'No se encontraron encuestas válidas para eliminar.');
            }

            // Crear backup masivo
            $this->crearBackupMasivo($encuestas);

            // Calcular estadísticas antes de eliminar
            $estadisticasTotales = $this->calcularEstadisticasMasivas($encuestas);

            // Eliminar encuestas
            $eliminadas = 0;
            $errores = [];

            foreach ($encuestas as $encuesta) {
                try {
                    $encuesta->delete();
                    $eliminadas++;

                    Log::info('Encuesta eliminada en proceso masivo', [
                        'user_id' => Auth::id(),
                        'encuesta_id' => $encuesta->id,
                        'titulo' => $encuesta->titulo
                    ]);
                } catch (Exception $e) {
                    $errores[] = "Error eliminando encuesta ID {$encuesta->id}: " . $e->getMessage();

                    Log::error('Error eliminando encuesta en proceso masivo', [
                        'user_id' => Auth::id(),
                        'encuesta_id' => $encuesta->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Preparar mensaje de resultado
            $mensaje = "Se eliminaron {$eliminadas} de " . count($encuestas) . " encuestas.";

            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', $errores);
            }

            Log::info('Eliminación masiva completada', [
                'user_id' => Auth::id(),
                'encuestas_solicitadas' => count($encuestaIds),
                'encuestas_eliminadas' => $eliminadas,
                'errores' => count($errores),
                'estadisticas_eliminadas' => $estadisticasTotales
            ]);

            return redirect()->route('encuestas.index')
                ->with('success', $mensaje);

        } catch (Exception $e) {
            Log::error('Error ejecutando eliminación masiva', [
                'user_id' => Auth::id(),
                'encuesta_ids' => $request->input('encuesta_ids'),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al ejecutar eliminación masiva: ' . $e->getMessage());
        }
    }

    /**
     * Calcular estadísticas para eliminación masiva
     */
    private function calcularEstadisticasMasivas($encuestas)
    {
        $totales = [
            'encuestas_count' => $encuestas->count(),
            'preguntas_count' => 0,
            'respuestas_count' => 0,
            'respuestas_usuarios_count' => 0,
            'bloques_envio_count' => 0,
            'tokens_acceso_count' => 0,
            'configuraciones_envio_count' => 0,
            'correos_enviados_count' => 0,
        ];

        foreach ($encuestas as $encuesta) {
            $totales['preguntas_count'] += $encuesta->preguntas->count();
            $totales['respuestas_count'] += $encuesta->preguntas->sum(function($p) { return $p->respuestas->count(); });
            $totales['respuestas_usuarios_count'] += $encuesta->respuestasUsuarios->count();
            $totales['bloques_envio_count'] += $encuesta->bloquesEnvio->count();
            $totales['tokens_acceso_count'] += $encuesta->tokensAcceso->count();
            $totales['configuraciones_envio_count'] += $encuesta->configuracionesEnvio->count();
            $totales['correos_enviados_count'] += $encuesta->correosEnviados->count();
        }

        return $totales;
    }

    /**
     * Crear backup masivo de encuestas
     */
    private function crearBackupMasivo($encuestas)
    {
        try {
            $backup = [
                'encuestas' => $encuestas->toArray(),
                'fecha_backup' => now()->toDateTimeString(),
                'usuario_backup' => Auth::id(),
                'accion' => 'eliminacion_masiva',
                'total_encuestas' => $encuestas->count()
            ];

            Log::info('Backup masivo de encuestas antes de eliminar', [
                'user_id' => Auth::id(),
                'total_encuestas' => $encuestas->count(),
                'encuesta_ids' => $encuestas->pluck('id')->toArray(),
                'backup' => $backup
            ]);

        } catch (Exception $e) {
            Log::warning('Error creando backup masivo', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
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
