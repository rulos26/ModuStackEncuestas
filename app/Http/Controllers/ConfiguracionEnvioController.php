<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionEnvio;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Empleado;
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

            $encuestas = Encuesta::where('empresa_id', $empresaId)
                ->where('estado', '!=', 'borrador')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'titulo', 'estado', 'created_at']);

            // Obtener configuraciones existentes
            $configuraciones = ConfiguracionEnvio::where('empresa_id', $empresaId)
                ->pluck('encuesta_id')
                ->toArray();

            // Marcar encuestas que ya tienen configuración
            $encuestas->each(function ($encuesta) use ($configuraciones) {
                $encuesta->configurada = in_array($encuesta->id, $configuraciones);
            });

            return response()->json([
                'success' => true,
                'data' => $encuestas
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo encuestas por empresa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener encuestas'
            ], 500);
        }
    }

    /**
     * Obtener configuración existente
     */
    public function getConfiguracion(Request $request): JsonResponse
    {
        try {
            $empresaId = $request->input('empresa_id');
            $encuestaId = $request->input('encuesta_id');

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
                'message' => 'Error al obtener configuración'
            ], 500);
        }
    }

    /**
     * Guardar configuración de envío
     */
    public function store(Request $request)
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
                'encuestas.*.tipo_envio' => 'required|in:manual,programado',
                'encuestas.*.plantilla' => 'nullable|string|max:255',
                'encuestas.*.activo' => 'boolean',
                // Validaciones para envío programado (solo si se envía el campo)
                'encuestas.*.fecha_envio' => 'nullable|date|after_or_equal:today',
                'encuestas.*.hora_envio' => 'nullable|date_format:H:i',
                'encuestas.*.tipo_destinatario' => 'nullable|in:empleados,clientes,proveedores,personalizado',
                'encuestas.*.numero_bloques' => 'nullable|integer|min:1|max:10',
                'encuestas.*.correo_prueba' => 'nullable|email',
                'encuestas.*.modo_prueba' => 'boolean'
            ]);

            // Validación personalizada para campos requeridos cuando es programado
            foreach ($request->input('encuestas', []) as $index => $encuestaData) {
                if ($encuestaData['tipo_envio'] === 'programado') {
                    if (empty($encuestaData['fecha_envio'])) {
                        $validator->errors()->add("encuestas.{$index}.fecha_envio", 'La fecha de envío es requerida para envío programado.');
                    }
                    if (empty($encuestaData['hora_envio'])) {
                        $validator->errors()->add("encuestas.{$index}.hora_envio", 'La hora de envío es requerida para envío programado.');
                    }
                    if (empty($encuestaData['tipo_destinatario'])) {
                        $validator->errors()->add("encuestas.{$index}.tipo_destinatario", 'El tipo de destinatario es requerido para envío programado.');
                    }
                    if (empty($encuestaData['numero_bloques'])) {
                        $validator->errors()->add("encuestas.{$index}.numero_bloques", 'El número de bloques es requerido para envío programado.');
                    }
                }
            }

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $empresaId = $request->input('empresa_id');
            $encuestas = $request->input('encuestas');
            $configuracionesGuardadas = [];

            foreach ($encuestas as $encuestaData) {
                // Eliminar configuración existente si la hay
                ConfiguracionEnvio::where('empresa_id', $empresaId)
                    ->where('encuesta_id', $encuestaData['encuesta_id'])
                    ->delete();

                // Preparar datos de configuración
                $datosConfiguracion = [
                    'empresa_id' => $empresaId,
                    'encuesta_id' => $encuestaData['encuesta_id'],
                    'nombre_remitente' => $encuestaData['nombre_remitente'],
                    'correo_remitente' => $encuestaData['correo_remitente'],
                    'asunto' => $encuestaData['asunto'],
                    'cuerpo_mensaje' => $encuestaData['cuerpo_mensaje'],
                    'tipo_envio' => $encuestaData['tipo_envio'],
                    'plantilla' => $encuestaData['plantilla'] ?? null,
                    'activo' => $encuestaData['activo'] ?? true,
                ];

                // Agregar campos específicos para envío programado
                if ($encuestaData['tipo_envio'] === 'programado') {
                    $datosConfiguracion = array_merge($datosConfiguracion, [
                        'fecha_envio' => $encuestaData['fecha_envio'],
                        'hora_envio' => $encuestaData['hora_envio'],
                        'tipo_destinatario' => $encuestaData['tipo_destinatario'],
                        'numero_bloques' => $encuestaData['numero_bloques'],
                        'correo_prueba' => $encuestaData['correo_prueba'] ?? null,
                        'modo_prueba' => $encuestaData['modo_prueba'] ?? false,
                        'estado_programacion' => 'pendiente'
                    ]);
                }

                // Crear nueva configuración
                $configuracion = ConfiguracionEnvio::create($datosConfiguracion);
                $configuracionesGuardadas[] = $configuracion;

                Log::info('Configuración de envío guardada', [
                    'configuracion_id' => $configuracion->id,
                    'empresa_id' => $empresaId,
                    'encuesta_id' => $encuestaData['encuesta_id'],
                    'tipo_envio' => $encuestaData['tipo_envio']
                ]);
            }

            DB::commit();

            // Redirigir según el tipo de envío
            $tieneProgramado = collect($encuestas)->contains('tipo_envio', 'programado');

            if ($tieneProgramado) {
                // Si hay envíos programados, ir al resumen
                return redirect()->route('configuracion-envio.resumen', ['empresa_id' => $empresaId])
                    ->with('success', 'Configuración guardada exitosamente. Los envíos programados se ejecutarán en la fecha y hora especificadas.');
            } else {
                // Si solo hay envíos manuales, ir al listado de encuestas
                return redirect()->route('encuestas.index')
                    ->with('success', 'Configuración guardada exitosamente. Los envíos manuales están listos para ejecutar.');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error guardando configuración de envío: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al guardar la configuración: ' . $e->getMessage())
                ->withInput();
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
                'tipo_envio' => 'required|in:manual,programado',
                'plantilla' => 'nullable|string|max:255',
                'activo' => 'boolean',
                // Validaciones para envío programado
                'fecha_envio' => 'required_if:tipo_envio,programado|date|after_or_equal:today',
                'hora_envio' => 'required_if:tipo_envio,programado|date_format:H:i',
                'tipo_destinatario' => 'required_if:tipo_envio,programado|in:empleados,clientes,proveedores,personalizado',
                'numero_bloques' => 'required_if:tipo_envio,programado|integer|min:1|max:10',
                'correo_prueba' => 'nullable|email',
                'modo_prueba' => 'boolean'
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
     * Mostrar vista de configuración (Wizard)
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
        $tiposDestinatario = ConfiguracionEnvio::getTiposDestinatario();

        // Generar el enlace base para las encuestas
        $link_encuesta = route('encuestas.publica', ['slug' => 'SLUG_PLACEHOLDER']);

        // Obtener estadísticas de destinatarios para sugerencias
        $estadisticasDestinatarios = $this->obtenerEstadisticasDestinatarios($empresaId);

        return view('configuracion_envio.configurar', compact(
            'empresa',
            'encuestas',
            'tiposEnvio',
            'tiposDestinatario',
            'link_encuesta',
            'estadisticasDestinatarios'
        ));
    }

    /**
     * Obtener estadísticas de destinatarios para sugerencias
     */
    private function obtenerEstadisticasDestinatarios($empresaId): array
    {
        $empleados = Empleado::where('empresa_id', $empresaId)->count();

        return [
            'empleados' => $empleados,
            'clientes' => 0, // Implementar cuando se tenga la tabla de clientes
            'proveedores' => 0, // Implementar cuando se tenga la tabla de proveedores
        ];
    }

    /**
     * Enviar correo de prueba
     */
    public function enviarPrueba(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'configuracion_id' => 'required|exists:configuracion_envios,id',
                'correo_prueba' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $configuracion = ConfiguracionEnvio::with(['encuesta', 'empresa'])->findOrFail($request->configuracion_id);

            // Actualizar correo de prueba
            $configuracion->update([
                'correo_prueba' => $request->correo_prueba,
                'modo_prueba' => true
            ]);

            // Dispatch del job para envío de prueba
            \App\Jobs\EnviarCorreosProgramados::dispatch($configuracion->id);

            return response()->json([
                'success' => true,
                'message' => 'Correo de prueba enviado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando correo de prueba: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo de prueba'
            ], 500);
        }
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

        // Agregar información de destinatarios a cada configuración
        $configuraciones->each(function ($configuracion) use ($empresaId) {
            $configuracion->destinatarios_info = $this->obtenerInfoDestinatarios($configuracion, $empresaId);
        });

        return view('configuracion_envio.resumen', compact('empresa', 'configuraciones'));
    }

    /**
     * Obtener información de destinatarios para una configuración
     */
    private function obtenerInfoDestinatarios($configuracion, $empresaId): array
    {
        $info = [
            'total' => 0,
            'tipo' => $configuracion->tipo_destinatario ?? 'empleados',
            'detalle' => []
        ];

        if ($configuracion->tipo_destinatario === 'empleados') {
            $empleados = Empleado::where('empresa_id', $empresaId)
                ->select('id', 'nombre', 'apellido', 'correo_electronico')
                ->get();

            $info['total'] = $empleados->count();
            $info['detalle'] = $empleados->take(5)->map(function ($empleado) {
                return [
                    'nombre' => $empleado->nombre . ' ' . $empleado->apellido,
                    'email' => $empleado->correo_electronico
                ];
            })->toArray();

            if ($empleados->count() > 5) {
                $info['detalle'][] = [
                    'nombre' => '... y ' . ($empleados->count() - 5) . ' más',
                    'email' => ''
                ];
            }
        }

        return $info;
    }

    /**
     * Editar configuración
     */
    public function editar($id)
    {
        $configuracion = ConfiguracionEnvio::with(['encuesta', 'empresa'])->findOrFail($id);

        $empresa = DB::table('empresas_clientes')->where('id', $configuracion->empresa_id)->first();

        $tiposEnvio = ConfiguracionEnvio::getTiposEnvio();
        $tiposDestinatario = ConfiguracionEnvio::getTiposDestinatario();

        // Generar el enlace base para las encuestas
        $link_encuesta = route('encuestas.publica', ['slug' => 'SLUG_PLACEHOLDER']);

        // Obtener estadísticas de destinatarios
        $estadisticasDestinatarios = $this->obtenerEstadisticasDestinatarios($configuracion->empresa_id);

        return view('configuracion_envio.configurar', compact(
            'configuracion',
            'empresa',
            'tiposEnvio',
            'tiposDestinatario',
            'link_encuesta',
            'estadisticasDestinatarios'
        ));
    }
}
