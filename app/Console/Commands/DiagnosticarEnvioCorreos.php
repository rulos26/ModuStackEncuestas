<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\SentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class DiagnosticarEnvioCorreos extends Command
{
    protected $signature = 'diagnosticar:envio-correos {encuesta_id?}';
    protected $description = 'Diagnosticar problemas de envío de correos';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTICANDO ENVÍO DE CORREOS');
        $this->line('');

        try {
            $encuestaId = $this->argument('encuesta_id');

            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                    return 1;
                }
                $this->diagnosticarEncuesta($encuesta);
            } else {
                $this->diagnosticarTodasLasEncuestas();
            }

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en diagnóstico: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Diagnosticar una encuesta específica
     */
    private function diagnosticarEncuesta($encuesta)
    {
        $this->line("📋 DIAGNOSTICANDO ENCUESTA:");
        $this->line("   • ID: {$encuesta->id}");
        $this->line("   • Título: {$encuesta->titulo}");
        $this->line("   • Estado: {$encuesta->estado}");
        $this->line("   • Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->line('');

        // 1. Verificar configuración de correo
        $this->verificarConfiguracionCorreo();

        // 2. Verificar empleados disponibles
        $this->verificarEmpleados($encuesta);

        // 3. Verificar correos enviados
        $this->verificarCorreosEnviados($encuesta);

        // 4. Verificar correos pendientes
        $this->verificarCorreosPendientes($encuesta);

        // 5. Probar envío de correo de prueba
        $this->probarEnvioCorreo($encuesta);
    }

    /**
     * Diagnosticar todas las encuestas
     */
    private function diagnosticarTodasLasEncuestas()
    {
        $this->line('📋 DIAGNOSTICANDO TODAS LAS ENCUESTAS');
        $this->line('');

        $encuestas = Encuesta::where('estado', 'publicada')->get();

        if ($encuestas->isEmpty()) {
            $this->warn('⚠️  No hay encuestas publicadas');
            return;
        }

        $this->line("📊 Total de encuestas publicadas: {$encuestas->count()}");
        $this->line('');

        foreach ($encuestas as $encuesta) {
            $this->line("🔍 Encuesta ID {$encuesta->id}: {$encuesta->titulo}");

            $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();
            $empleados = Empleado::count();

            $this->line("   📧 Correos enviados: {$correosEnviados}");
            $this->line("   👥 Total empleados: {$empleados}");
            $this->line('');
        }
    }

    /**
     * Verificar configuración de correo
     */
    private function verificarConfiguracionCorreo()
    {
        $this->line('1️⃣ VERIFICANDO CONFIGURACIÓN DE CORREO:');

        $configuraciones = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name')
        ];

        foreach ($configuraciones as $key => $value) {
            if ($value) {
                $this->line("   ✅ {$key}: {$value}");
            } else {
                $this->error("   ❌ {$key}: NO CONFIGURADO");
            }
        }

        // Verificar si la plantilla existe
        $templatePath = resource_path('views/emails/encuesta.blade.php');
        if (file_exists($templatePath)) {
            $this->line("   ✅ Plantilla de correo: Existe");
        } else {
            $this->error("   ❌ Plantilla de correo: NO EXISTE");
        }

        $this->line('');
    }

    /**
     * Verificar empleados disponibles
     */
    private function verificarEmpleados($encuesta)
    {
        $this->line('2️⃣ VERIFICANDO EMPLEADOS:');

        $totalEmpleados = Empleado::count();
        $empleadosConCorreo = Empleado::whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
            ->count();

        $this->line("   📊 Total empleados: {$totalEmpleados}");
        $this->line("   📧 Empleados con correo: {$empleadosConCorreo}");

        if ($empleadosConCorreo === 0) {
            $this->error("   ❌ No hay empleados con correo electrónico");
        } else {
            $this->line("   ✅ Hay empleados disponibles para envío");
        }

        $this->line('');
    }

    /**
     * Verificar correos enviados
     */
    private function verificarCorreosEnviados($encuesta)
    {
        $this->line('3️⃣ VERIFICANDO CORREOS ENVIADOS:');

        $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();
        $correosExitosos = SentMail::where('encuesta_id', $encuesta->id)
            ->where('status', 'sent')
            ->count();
        $correosFallidos = SentMail::where('encuesta_id', $encuesta->id)
            ->where('status', 'failed')
            ->count();

        $this->line("   📧 Total correos enviados: {$correosEnviados}");
        $this->line("   ✅ Correos exitosos: {$correosExitosos}");
        $this->line("   ❌ Correos fallidos: {$correosFallidos}");

        if ($correosFallidos > 0) {
            $this->warn("   ⚠️  Hay correos fallidos - revisar logs");
        }

        $this->line('');
    }

    /**
     * Verificar correos pendientes
     */
    private function verificarCorreosPendientes($encuesta)
    {
        $this->line('4️⃣ VERIFICANDO CORREOS PENDIENTES:');

        // Empleados que no han recibido correo
        $empleadosSinCorreo = Empleado::whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
                         ->whereNotExists(function ($query) use ($encuesta) {
                 $query->select(DB::raw(1))
                       ->from('sent_mails')
                       ->whereColumn('sent_mails.to', 'empleados.correo_electronico')
                       ->where('sent_mails.encuesta_id', $encuesta->id);
             })->count();

        $this->line("   📧 Correos pendientes: {$empleadosSinCorreo}");

        if ($empleadosSinCorreo === 0) {
            $this->line("   ✅ Todos los empleados han recibido correo");
        } else {
            $this->line("   ⚠️  Hay {$empleadosSinCorreo} correos pendientes");
        }

        $this->line('');
    }

    /**
     * Probar envío de correo
     */
    private function probarEnvioCorreo($encuesta)
    {
        $this->line('5️⃣ PROBANDO ENVÍO DE CORREO:');

        try {
            // Buscar un empleado con correo
            $empleado = Empleado::whereNotNull('correo_electronico')
                ->where('correo_electronico', '!=', '')
                ->first();

            if (!$empleado) {
                $this->error("   ❌ No hay empleados con correo para probar");
                return;
            }

            $this->line("   📧 Probando envío a: {$empleado->correo_electronico}");

            // Generar token y enlace
            $token = $encuesta->generarTokenAcceso();
            $enlace = route('encuestas.publica', $encuesta->slug) . '?token=' . $token;

            // Preparar datos
            $datosCorreo = [
                'nombre' => $empleado->nombre,
                'email' => $empleado->correo_electronico,
                'encuesta' => $encuesta->titulo,
                'enlace' => $enlace,
                'fecha_limite' => $encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y H:i') : 'Sin fecha límite'
            ];

            // Enviar correo de prueba
            Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($empleado, $encuesta) {
                $message->to($empleado->correo_electronico, $empleado->nombre)
                        ->subject('PRUEBA - Nueva encuesta disponible: ' . $encuesta->titulo);
            });

            $this->line("   ✅ Correo de prueba enviado exitosamente");

            // Registrar en base de datos
            SentMail::create([
                'encuesta_id' => $encuesta->id,
                'to' => $empleado->correo_electronico,
                'subject' => 'PRUEBA - Nueva encuesta disponible: ' . $encuesta->titulo,
                'body' => view('emails.encuesta', $datosCorreo)->render(),
                'status' => 'sent',
                'sent_by' => 1,
                'sent_at' => now()
            ]);

            $this->line("   ✅ Registro guardado en base de datos");

        } catch (Exception $e) {
            $this->error("   ❌ Error enviando correo de prueba: " . $e->getMessage());

            Log::error('Error en prueba de envío de correo', [
                'encuesta_id' => $encuesta->id,
                'empleado_email' => $empleado->correo_electronico ?? 'N/A',
                'error' => $e->getMessage()
            ]);
        }

        $this->line('');
    }
}
