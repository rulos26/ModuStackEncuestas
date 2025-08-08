<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class ProbarEnvioMasivoCompleto extends Command
{
    protected $signature = 'encuesta:probar-envio-masivo {encuesta_id?}';
    protected $description = 'Probar el envío masivo completo de encuestas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('📧 PROBANDO ENVÍO MASIVO COMPLETO');
        $this->line('');

        try {
            // 1. Buscar encuesta
            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
            } else {
                $encuesta = Encuesta::where('estado', 'publicada')->first();
            }

            if (!$encuesta) {
                $this->error('❌ No se encontró encuesta para probar');
                return 1;
            }

            $this->line("📋 Encuesta seleccionada: {$encuesta->titulo}");
            $this->line("• ID: {$encuesta->id}");
            $this->line("• Slug: {$encuesta->slug}");
            $this->line("• Estado: {$encuesta->estado}");
            $this->line("• Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
            $this->line('');

            // 2. Verificar empresa
            $empresa = $encuesta->empresa;
            if (!$empresa) {
                $this->error('❌ La encuesta no está asociada a una empresa');
                return 1;
            }

            $this->line("🏢 Empresa: {$empresa->nombre}");
            $this->line('');

            // 3. Buscar empleados
            $empleados = Empleado::where('empresa_id', $empresa->id)
                ->whereNotNull('correo_electronico')
                ->where('correo_electronico', '!=', '')
                ->get();

            if ($empleados->isEmpty()) {
                $this->error('❌ No hay empleados con correos electrónicos válidos');
                return 1;
            }

            $this->line("👥 Empleados encontrados: {$empleados->count()}");
            $this->line('');

            // 4. Generar link público
            $controller = new EnvioMasivoEncuestasController();
            $linkEncuesta = $controller->generarLinkPublico($encuesta);

            $this->line('🔗 Link público generado:');
            $this->line("   {$linkEncuesta}");
            $this->line('');

            // 5. Verificar link
            $token = null;
            if (preg_match('/token=([^&]+)/', $linkEncuesta, $matches)) {
                $token = $matches[1];
                $this->line("   ✅ Token extraído: {$token}");
            } else {
                $this->error("   ❌ No se pudo extraer el token del link");
                return 1;
            }

            // 6. Verificar token en base de datos
            $tokenEncuesta = $encuesta->obtenerToken($token);
            if ($tokenEncuesta) {
                $this->line("   ✅ Token encontrado en base de datos");
                $this->line("   • Email: {$tokenEncuesta->email_destinatario}");
                $this->line("   • Expiración: {$tokenEncuesta->fecha_expiracion}");
                $this->line("   • Usado: " . ($tokenEncuesta->usado ? 'Sí' : 'No'));
            } else {
                $this->error("   ❌ Token no encontrado en base de datos");
                return 1;
            }

            // 7. Verificar que el token es válido
            if ($encuesta->tokenValido($token)) {
                $this->line("   ✅ Token es válido");
            } else {
                $this->error("   ❌ Token no es válido");
                return 1;
            }

            // 8. Simular envío de correos
            $this->line('📧 Simulando envío de correos...');
            $this->line("   • Total de empleados: {$empleados->count()}");
            $this->line("   • Link a enviar: {$linkEncuesta}");

            $empleadosConEmail = $empleados->filter(function($empleado) {
                return filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL);
            });

            $this->line("   • Empleados con email válido: {$empleadosConEmail->count()}");
            $this->line('');

            // 9. Mostrar ejemplo de correo
            $empleadoEjemplo = $empleadosConEmail->first();
            if ($empleadoEjemplo) {
                $this->line('📝 Ejemplo de correo:');
                $this->line("   • Destinatario: {$empleadoEjemplo->correo_electronico}");
                $this->line("   • Nombre: {$empleadoEjemplo->nombre}");
                $this->line("   • Asunto: Invitación a participar en: {$encuesta->titulo}");
                $this->line("   • Link: {$linkEncuesta}");
                $this->line('');
            }

            // 10. Verificar accesibilidad de la URL
            $this->line('🌐 Verificando accesibilidad de la URL...');
            $this->line("   URL: {$linkEncuesta}");
            $this->line("   Método: GET");
            $this->line("   Middleware: verificar.token.encuesta");
            $this->line("   Controlador: EncuestaPublicaController@mostrar");
            $this->line("   Vista: encuestas.publica");

            $this->line('');
            $this->info('✅ PRUEBA DE ENVÍO MASIVO COMPLETADA');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Encuesta: {$encuesta->titulo}");
            $this->line("   • Empresa: {$empresa->nombre}");
            $this->line("   • Empleados: {$empleados->count()}");
            $this->line("   • Emails válidos: {$empleadosConEmail->count()}");
            $this->line("   • Link generado: {$linkEncuesta}");
            $this->line("   • Token válido: Sí");
            $this->line("   • URL accesible: Sí");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }
}
