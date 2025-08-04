<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionEnvio;
use App\Models\Empresa;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConfiguracionEnvioController extends Controller
{
    /**
     * Mostrar la pantalla inicial con selección de empresa
     */
    public function index()
    {
        $empresas = DB::table('empresas_clientes')->orderBy('nombre', 'asc')->get();

        return view('configuracion_envio.index', compact('empresas'));
    }

    /**
     * Obtener encuestas por empresa con estado de configuración
     */
    public function getEncuestasPorEmpresa(Request $request): JsonResponse
    {
        try {
            $empresaId = $request->input('empresa_id');

            if (!$empresaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de empresa requerido'
                ], 400);
            }

            // Verificar que la empresa existe
            $empresa = DB::table('empresas_clientes')->where('id', $empresaId)->first();
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            // Obtener encuestas de la empresa
            $encuestas = Encuesta::where('empresa_id', $empresaId)
                ->orderBy('titulo')
                ->get();

            // Obtener configuraciones existentes
            $configuraciones = ConfiguracionEnvio::where('empresa_id', $empresaId)
                ->pluck('encuesta_id')
                ->toArray();

            // Agregar estado de configuración a cada encuesta
            $encuestasConEstado = $encuestas->map(function ($encuesta) use ($configuraciones) {
                $encuesta->estado_configuracion = in_array($encuesta->id, $configuraciones) ? 'Configurado' : 'No Configurado';
                $encuesta->configurado = in_array($encuesta->id, $configuraciones);
                return $encuesta;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'empresa' => $empresa,
                    'encuestas' => $encuestasConEstado
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo encuestas por empresa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener configuración existente para una encuesta
     */
    public function getConfiguracion(Request $request): JsonResponse
    {
        try {
            $encuestaId = $request->input('encuesta_id');
            $empresaId = $request->input('empresa_id');

            if (!$encuestaId || !$empresaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de encuesta y empresa requeridos'
                ], 400);
            }

            $configuracion = ConfiguracionEnvio::where('empresa_id', $empresaId)
                ->where('encuesta_id', $encuestaId)
                ->first();

            return response()->json([
                'success' => true,
                'data' => $configuracion
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo configuración: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Guardar nueva configuración
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'empresa_id' => 'required|exists:empresas_clientes,id',
                'encuestas' => 'required|array|min:1',
                'encuestas.*.encuesta_id' => 'required|exists:encuestas,id',
                'encuestas.*.nombre_remitente' => 'required|string|max:255',
                'encuestas.*.correo_remitente' => 'required|email|max:255',
                'encuestas.*.asunto' => 'required|string|max:255',
                'encuestas.*.cuerpo_mensaje' => 'required|string',
                'encuestas.*.tipo_envio' => 'required|in:automatico,manual,programado',
                'encuestas.*.plantilla' => 'nullable|string|max:255',
                'encuestas.*.activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $empresaId = $request->input('empresa_id');
            $encuestas = $request->input('encuestas');
            $configuracionesGuardadas = [];

            foreach ($encuestas as $encuestaData) {
                // Verificar si ya existe una configuración
                $configuracionExistente = ConfiguracionEnvio::where('empresa_id', $empresaId)
                    ->where('encuesta_id', $encuestaData['encuesta_id'])
                    ->first();

                if ($configuracionExistente) {
                    // Actualizar configuración existente
                    $configuracionExistente->update([
                        'nombre_remitente' => $encuestaData['nombre_remitente'],
                        'correo_remitente' => $encuestaData['correo_remitente'],
                        'asunto' => $encuestaData['asunto'],
                        'cuerpo_mensaje' => $encuestaData['cuerpo_mensaje'],
                        'tipo_envio' => $encuestaData['tipo_envio'],
                        'plantilla' => $encuestaData['plantilla'] ?? null,
                        'activo' => $encuestaData['activo'] ?? true
                    ]);

                    $configuracionesGuardadas[] = $configuracionExistente;
                } else {
                    // Crear nueva configuración
                    $configuracion = ConfiguracionEnvio::create([
                        'empresa_id' => $empresaId,
                        'encuesta_id' => $encuestaData['encuesta_id'],
                        'nombre_remitente' => $encuestaData['nombre_remitente'],
                        'correo_remitente' => $encuestaData['correo_remitente'],
                        'asunto' => $encuestaData['asunto'],
                        'cuerpo_mensaje' => $encuestaData['cuerpo_mensaje'],
                        'tipo_envio' => $encuestaData['tipo_envio'],
                        'plantilla' => $encuestaData['plantilla'] ?? null,
                        'activo' => $encuestaData['activo'] ?? true
                    ]);

                    $configuracionesGuardadas[] = $configuracion;
                }
            }

            DB::commit();

            Log::info('Configuraciones de envío guardadas exitosamente', [
                'empresa_id' => $empresaId,
                'total_configuraciones' => count($configuracionesGuardadas)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones guardadas exitosamente',
                'data' => [
                    'configuraciones_guardadas' => count($configuracionesGuardadas)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error guardando configuraciones de envío: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las configuraciones'
            ], 500);
        }
    }

    /**
     * Actualizar configuración existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $configuracion = ConfiguracionEnvio::find($id);

            if (!$configuracion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre_remitente' => 'required|string|max:255',
                'correo_remitente' => 'required|email|max:255',
                'asunto' => 'required|string|max:255',
                'cuerpo_mensaje' => 'required|string',
                'tipo_envio' => 'required|in:automatico,manual,programado',
                'plantilla' => 'nullable|string|max:255',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $configuracion->update($request->all());

            Log::info('Configuración de envío actualizada', [
                'configuracion_id' => $id,
                'empresa_id' => $configuracion->empresa_id,
                'encuesta_id' => $configuracion->encuesta_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'data' => $configuracion
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando configuración de envío: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración'
            ], 500);
        }
    }

    /**
     * Mostrar vista de configuración
     */
    public function configurar(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $encuestaIds = $request->input('encuesta_ids', []);

        if (!$empresaId || empty($encuestaIds)) {
            return redirect()->route('configuracion-envio.index')
                ->with('error', 'Debe seleccionar una empresa y al menos una encuesta');
        }

        $empresa = DB::table('empresas_clientes')->where('id', $empresaId)->first();
        $encuestas = Encuesta::whereIn('id', $encuestaIds)->get();
        $tiposEnvio = ConfiguracionEnvio::getTiposEnvio();

        // Generar el enlace base para las encuestas
        $link_encuesta = route('encuesta.publica', ['token' => 'TOKEN_PLACEHOLDER']);

        return view('configuracion_envio.configurar', compact('empresa', 'encuestas', 'tiposEnvio', 'link_encuesta'));
    }

    /**
     * Mostrar resumen de configuraciones
     */
    public function resumen(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        $empresa = DB::table('empresas_clientes')->where('id', $empresaId)->first();

        if (!$empresa) {
            return redirect()->route('configuracion-envio.index')
                ->with('error', 'Empresa no encontrada');
        }

        $configuraciones = ConfiguracionEnvio::with('encuesta')
            ->where('empresa_id', $empresaId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('configuracion_envio.resumen', compact('empresa', 'configuraciones'));
    }
}
