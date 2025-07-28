<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\User;
use App\Models\SentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaEnvioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['empresa', 'preguntas'])->findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para configurar el envío de esta encuesta.');
            }

            // Verificar que la encuesta esté lista para envío
            if (!$encuesta->puedeEnviarseMasivamente()) {
                return redirect()->route('encuestas.show', $encuestaId)
                    ->with('warning', 'La encuesta no está lista para envío masivo. Verifique que esté en estado borrador y tenga validación completada.');
            }

            // Obtener empleados disponibles
            $empleados = Empleado::orderBy('nombre')->get();

            // Obtener usuarios del sistema
            $usuarios = User::where('id', '!=', Auth::id())->orderBy('name')->get();

            // Calcular estadísticas
            $totalEmpleados = $empleados->count();
            $totalUsuarios = $usuarios->count();
            $totalDisponibles = $totalEmpleados + $totalUsuarios;
            $encuestasDisponibles = $encuesta->numero_encuestas ?? 0;

            return view('encuestas.envio.create', compact(
                'encuesta',
                'empleados',
                'usuarios',
                'totalEmpleados',
                'totalUsuarios',
                'totalDisponibles',
                'encuestasDisponibles'
            ));

        } catch (Exception $e) {
            Log::error('Error accediendo a configuración de envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.index')
                ->with('error', 'Error al cargar la configuración de envío: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $encuestaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para enviar esta encuesta.');
            }

            // Validar datos
            $request->validate([
                'destinatarios' => 'required|array|min:1',
                'destinatarios.*' => 'required|string',
                'fecha_envio' => 'required|date|after:now',
                'hora_envio' => 'required|date_format:H:i',
                'plantilla_correo' => 'nullable|string|max:5000',
                'asunto_correo' => 'required|string|max:255',
                'enviar_ahora' => 'nullable|boolean'
            ], [
                'destinatarios.required' => 'Debe seleccionar al menos un destinatario.',
                'destinatarios.min' => 'Debe seleccionar al menos un destinatario.',
                'fecha_envio.required' => 'La fecha de envío es obligatoria.',
                'fecha_envio.after' => 'La fecha de envío debe ser posterior a la fecha actual.',
                'hora_envio.required' => 'La hora de envío es obligatoria.',
                'asunto_correo.required' => 'El asunto del correo es obligatorio.'
            ]);

            $destinatarios = $request->destinatarios;
            $fechaEnvio = $request->fecha_envio . ' ' . $request->hora_envio;
            $enviarAhora = $request->has('enviar_ahora');

            // Verificar que el número de destinatarios coincida con el número de encuestas
            if (count($destinatarios) > $encuesta->numero_encuestas) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El número de destinatarios no puede exceder el número de encuestas configurado.');
            }

            // Actualizar encuesta
            $encuesta->update([
                'encuestas_enviadas' => count($destinatarios),
                'encuestas_pendientes' => $encuesta->numero_encuestas - count($destinatarios),
                'plantilla_correo' => $request->plantilla_correo,
                'asunto_correo' => $request->asunto_correo,
                'estado' => 'enviada'
            ]);

            // Enviar correos
            $enviados = 0;
            $errores = [];

            foreach ($destinatarios as $destinatario) {
                try {
                    $this->enviarCorreoEncuesta($encuesta, $destinatario, $request->asunto_correo, $request->plantilla_correo);
                    $enviados++;
                } catch (Exception $e) {
                    $errores[] = "Error enviando a {$destinatario}: " . $e->getMessage();
                    Log::error('Error enviando correo de encuesta', [
                        'encuesta_id' => $encuestaId,
                        'destinatario' => $destinatario,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('Envío masivo de encuesta completado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'total_destinatarios' => count($destinatarios),
                'enviados_exitosos' => $enviados,
                'errores' => count($errores)
            ]);

            $mensaje = "Envío configurado correctamente. {$enviados} correos enviados exitosamente.";
            if (!empty($errores)) {
                $mensaje .= " Se encontraron " . count($errores) . " errores.";
            }

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('success', $mensaje)
                ->with('errores_envio', $errores);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error en envío masivo de encuesta', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al configurar el envío: ' . $e->getMessage());
        }
    }

    /**
     * Agregar usuario manualmente
     */
    public function agregarUsuario(Request $request, $encuestaId)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'cargo' => 'nullable|string|max:255'
            ]);

            // Crear usuario
            $user = User::create([
                'name' => $request->nombre,
                'email' => $request->email,
                'password' => bcrypt(str_random(10)), // Contraseña temporal
                'email_verified_at' => now()
            ]);

            // Asignar rol por defecto
            $user->assignRole('Cliente');

            // Crear empleado asociado
            Empleado::create([
                'nombre' => $request->nombre,
                'cargo' => $request->cargo ?? 'Usuario del sistema',
                'telefono' => '0000000000', // Teléfono por defecto
                'correo_electronico' => $request->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario agregado correctamente',
                'user' => $user
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar correo individual de encuesta
     */
    private function enviarCorreoEncuesta($encuesta, $destinatario, $asunto, $plantilla)
    {
        // Generar token único para la encuesta
        $token = $encuesta->generarTokenAcceso();

        // URL de la encuesta
        $urlEncuesta = route('encuestas.publica', $encuesta->slug);

        // Plantilla por defecto si no se proporciona
        if (empty($plantilla)) {
            $plantilla = "
                <h2>Invitación a participar en encuesta</h2>
                <p>Hola,</p>
                <p>Has sido invitado a participar en la siguiente encuesta:</p>
                <h3>{$encuesta->titulo}</h3>
                <p>Para acceder a la encuesta, haz clic en el siguiente enlace:</p>
                <p><a href='{$urlEncuesta}?token={$token}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Participar en la encuesta</a></p>
                <p>Este enlace es único y personalizado para ti.</p>
                <p>Gracias por tu participación.</p>
            ";
        }

        // Reemplazar variables en la plantilla
        $plantilla = str_replace(
            ['{NOMBRE_ENCUESTA}', '{URL_ENCUESTA}', '{TOKEN}'],
            [$encuesta->titulo, $urlEncuesta, $token],
            $plantilla
        );

        // Enviar correo
        Mail::send([], [], function ($message) use ($destinatario, $asunto, $plantilla) {
            $message->to($destinatario)
                    ->subject($asunto)
                    ->html($plantilla);
        });

        // Registrar envío
        SentMail::create([
            'to' => $destinatario,
            'subject' => $asunto,
            'body' => $plantilla,
            'sent_by' => Auth::id(),
            'encuesta_id' => $encuesta->id
        ]);
    }
}
