<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\SentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestEmailSending extends Command
{
    protected $signature = 'test:email-sending {encuesta_id?} {--email=}';
    protected $description = 'Probar el envío de correos del dashboard';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $testEmail = $this->option('email');

        if (!$encuestaId) {
            $encuesta = Encuesta::first();
            if (!$encuesta) {
                $this->error('No hay encuestas disponibles para probar');
                return 1;
            }
            $encuestaId = $encuesta->id;
        }

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            $this->error("Encuesta con ID {$encuestaId} no encontrada");
            return 1;
        }

        $this->info("=== PRUEBA DE ENVÍO DE CORREOS ===");
        $this->info("Encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->line('');

        // Probar envío individual
        $this->testIndividualSending($encuesta, $testEmail);

        // Probar envío masivo
        $this->testMassSending($encuesta);

        // Probar envío seleccionado
        $this->testSelectedSending($encuesta);

        $this->info('✅ Pruebas de envío completadas');
        return 0;
    }

    private function testIndividualSending($encuesta, $testEmail = null)
    {
        $this->info('📧 PROBANDO ENVÍO INDIVIDUAL:');

        // Obtener empleados disponibles
        $empleados = Empleado::all();

        if ($empleados->isEmpty()) {
            $this->warn('  No hay empleados disponibles para enviar correos');
            return;
        }

        // Usar email de prueba si se proporciona
        if ($testEmail) {
            $empleado = $empleados->first();
            $empleado->correo_electronico = $testEmail;
            $this->line("  📤 Enviando correo de prueba a: {$testEmail}");
        } else {
            $empleado = $empleados->first();
            $this->line("  📤 Enviando correo a: {$empleado->correo_electronico}");
        }

        try {
            // Generar token
            $token = $encuesta->generarTokenAcceso();
            $enlace = route('encuestas.publica', $encuesta->slug) . '?token=' . $token;

            // Preparar datos del correo
            $datosCorreo = [
                'nombre' => $empleado->nombre,
                'email' => $empleado->correo_electronico,
                'encuesta' => $encuesta->titulo,
                'enlace' => $enlace,
                'fecha_limite' => $encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y H:i') : 'Sin fecha límite'
            ];

            // Enviar correo
            Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($empleado, $encuesta) {
                $message->to($empleado->correo_electronico, $empleado->nombre)
                        ->subject('Nueva encuesta disponible: ' . $encuesta->titulo);
            });

            // Registrar envío
            SentMail::create([
                'encuesta_id' => $encuesta->id,
                'to' => $empleado->correo_electronico,
                'subject' => 'Nueva encuesta disponible: ' . $encuesta->titulo,
                'body' => view('emails.encuesta', $datosCorreo)->render(),
                'status' => 'sent',
                'sent_by' => auth()->id() ?? 1,
                'sent_at' => now()
            ]);

            $this->line("  ✅ Correo enviado exitosamente");
            $this->line("  🔗 Enlace generado: {$enlace}");

        } catch (\Exception $e) {
            $this->error("  ❌ Error enviando correo: {$e->getMessage()}");
            Log::error('Error en prueba de envío individual', [
                'encuesta_id' => $encuesta->id,
                'email' => $empleado->correo_electronico,
                'error' => $e->getMessage()
            ]);
        }

        $this->line('');
    }

    private function testMassSending($encuesta)
    {
        $this->info('📨 PROBANDO ENVÍO MASIVO:');

        $empleados = Empleado::all();

        if ($empleados->isEmpty()) {
            $this->warn('  No hay empleados disponibles');
            return;
        }

        $this->line("  📊 Total de empleados: {$empleados->count()}");

        $enviados = 0;
        $errores = 0;

        foreach ($empleados as $empleado) {
            try {
                // Verificar si ya se envió
                $yaEnviado = SentMail::where('encuesta_id', $encuesta->id)
                    ->where('to', $empleado->correo_electronico)
                    ->exists();

                if ($yaEnviado) {
                    $this->line("  ⏭️  Ya enviado a: {$empleado->correo_electronico}");
                    continue;
                }

                // Generar token
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

                // Enviar correo
                Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($empleado, $encuesta) {
                    $message->to($empleado->correo_electronico, $empleado->nombre)
                            ->subject('Nueva encuesta disponible: ' . $encuesta->titulo);
                });

                // Registrar envío
                SentMail::create([
                    'encuesta_id' => $encuesta->id,
                    'to' => $empleado->correo_electronico,
                    'subject' => 'Nueva encuesta disponible: ' . $encuesta->titulo,
                    'body' => view('emails.encuesta', $datosCorreo)->render(),
                    'status' => 'sent',
                    'sent_by' => auth()->id() ?? 1,
                    'sent_at' => now()
                ]);

                $enviados++;
                $this->line("  ✅ Enviado a: {$empleado->correo_electronico}");

            } catch (\Exception $e) {
                $errores++;
                $this->error("  ❌ Error enviando a {$empleado->correo_electronico}: {$e->getMessage()}");
            }
        }

        $this->line("  📈 Resumen: {$enviados} enviados, {$errores} errores");
        $this->line('');
    }

    private function testSelectedSending($encuesta)
    {
        $this->info('🎯 PROBANDO ENVÍO SELECCIONADO:');

        // Obtener empleados que no han recibido correo
        $empleadosSinCorreo = Empleado::whereNotExists(function ($query) use ($encuesta) {
            $query->select(\DB::raw(1))
                  ->from('sent_mails')
                  ->whereColumn('sent_mails.to', 'empleados.correo_electronico')
                  ->where('sent_mails.encuesta_id', $encuesta->id);
        })->take(3)->get(); // Solo probar con 3

        if ($empleadosSinCorreo->isEmpty()) {
            $this->warn('  No hay empleados pendientes de envío');
            return;
        }

        $this->line("  📋 Empleados seleccionados: {$empleadosSinCorreo->count()}");

        $correosIds = $empleadosSinCorreo->pluck('id')->toArray();

        foreach ($empleadosSinCorreo as $empleado) {
            try {
                // Generar token
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

                // Enviar correo
                Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($empleado, $encuesta) {
                    $message->to($empleado->correo_electronico, $empleado->nombre)
                            ->subject('Nueva encuesta disponible: ' . $encuesta->titulo);
                });

                // Registrar envío
                SentMail::create([
                    'encuesta_id' => $encuesta->id,
                    'to' => $empleado->correo_electronico,
                    'subject' => 'Nueva encuesta disponible: ' . $encuesta->titulo,
                    'body' => view('emails.encuesta', $datosCorreo)->render(),
                    'status' => 'sent',
                    'sent_by' => auth()->id() ?? 1,
                    'sent_at' => now()
                ]);

                $this->line("  ✅ Seleccionado enviado a: {$empleado->correo_electronico}");

            } catch (\Exception $e) {
                $this->error("  ❌ Error enviando seleccionado a {$empleado->correo_electronico}: {$e->getMessage()}");
            }
        }

        $this->line('');
    }
}
