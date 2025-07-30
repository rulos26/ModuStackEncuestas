<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Logica;
use App\Models\Empresa;
use App\Models\User;
use Exception;

class TesterFlujoCompletoEncuestas extends Command
{
    protected $signature = 'tester:flujo-completo {--debug} {--email=} {--cantidad=20}';
    protected $description = 'Testea todo el flujo de trabajo de encuestas automáticamente';

    private $encuesta;
    private $empresa;
    private $user;
    private $emailTest;
    private $cantidadUsuarios;
    private $debug;

    public function handle()
    {
        $this->debug = $this->option('debug');
        $this->emailTest = $this->option('email') ?? 'rulos26@gmail.com';
        $this->cantidadUsuarios = $this->option('cantidad');

        $this->info("🧪 TESTER DE FLUJO COMPLETO DE ENCUESTAS");
        $this->line('');
        $this->line("📧 Email de prueba: {$this->emailTest}");
        $this->line("👥 Cantidad de usuarios: {$this->cantidadUsuarios}");
        $this->line('');

        try {
            // 1. Preparar entorno
            $this->prepararEntorno();

            // 2. Crear encuesta
            $this->crearEncuesta();

            // 3. Crear preguntas por cada tipo
            $this->crearPreguntas();

            // 4. Configurar respuestas
            $this->configurarRespuestas();

            // 5. Configurar lógica condicional
            $this->configurarLogica();

            // 6. Configurar envío masivo
            $this->configurarEnvioMasivo();

            // 7. Verificar resultados
            $this->verificarResultados();

            // 8. Mostrar resumen
            $this->mostrarResumen();

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante el test: " . $e->getMessage());

            if ($this->debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function prepararEntorno()
    {
        $this->info("🔧 PREPARANDO ENTORNO:");

        // Verificar empresa
        $this->empresa = Empresa::first();
        if (!$this->empresa) {
            $this->empresa = Empresa::create([
                'nombre' => 'Empresa de Prueba - Tester',
                'nit' => '123456789',
                'direccion' => 'Dirección de Prueba',
                'telefono' => '3001234567',
                'email' => $this->emailTest,
                'pais_id' => 1,
                'departamento_id' => 1,
                'municipio_id' => 1
            ]);
            $this->line("   ✅ Empresa creada: {$this->empresa->nombre}");
        } else {
            $this->line("   ✅ Empresa existente: {$this->empresa->nombre}");
        }

        // Verificar usuario
        $this->user = User::first();
        if (!$this->user) {
            $this->error("   ❌ No hay usuarios en el sistema");
            throw new Exception("Se requiere al menos un usuario para crear encuestas");
        }
        $this->line("   ✅ Usuario: {$this->user->name}");

        $this->line('');
    }

    private function crearEncuesta()
    {
        $this->info("📝 CREANDO ENCUESTA:");

        $this->encuesta = Encuesta::create([
            'titulo' => 'Encuesta de Prueba - Tester Automático - ' . now()->format('Y-m-d H:i:s'),
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->user->id,
            'numero_encuestas' => $this->cantidadUsuarios,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'enviar_por_correo' => true,
            'envio_masivo_activado' => false,
            'estado' => 'borrador',
            'validacion_completada' => false,
            'habilitada' => true
        ]);

        $this->line("   ✅ Encuesta creada: '{$this->encuesta->titulo}'");
        $this->line("   - ID: {$this->encuesta->id}");
        $this->line("   - Estado: {$this->encuesta->estado}");
        $this->line('');

        if ($this->debug) {
            Log::info('TESTER - Encuesta creada', [
                'encuesta_id' => $this->encuesta->id,
                'titulo' => $this->encuesta->titulo
            ]);
        }
    }

    private function crearPreguntas()
    {
        $this->info("❓ CREANDO PREGUNTAS POR CADA TIPO:");

        $tiposPreguntas = [
            'respuesta_corta' => '¿Cuál es tu nombre completo?',
            'parrafo' => 'Describe tu experiencia laboral en detalle',
            'seleccion_unica' => '¿Cuál es tu nivel de educación?',
            'casillas_verificacion' => '¿Qué tecnologías conoces?',
            'lista_desplegable' => '¿En qué departamento trabajas?',
            'escala_lineal' => '¿Qué tan satisfecho estás con tu trabajo?',
            'fecha' => '¿Cuál es tu fecha de nacimiento?',
            'hora' => '¿A qué hora prefieres las reuniones?'
        ];

        $orden = 1;
        foreach ($tiposPreguntas as $tipo => $texto) {
            $pregunta = Pregunta::create([
                'encuesta_id' => $this->encuesta->id,
                'texto' => $texto,
                'tipo' => $tipo,
                'orden' => $orden,
                'obligatoria' => true
            ]);

            $this->line("   ✅ Pregunta {$orden}: {$texto} ({$tipo})");
            $orden++;
        }

        $this->line("   📊 Total preguntas creadas: " . count($tiposPreguntas));
        $this->line('');
    }

    private function configurarRespuestas()
    {
        $this->info("📋 CONFIGURANDO RESPUESTAS:");

        $preguntas = $this->encuesta->preguntas;

        foreach ($preguntas as $pregunta) {
            if ($pregunta->tipo === 'seleccion_unica') {
                $respuestas = [
                    'Primaria',
                    'Secundaria',
                    'Técnico',
                    'Universitario',
                    'Postgrado'
                ];
            } elseif ($pregunta->tipo === 'casillas_verificacion') {
                $respuestas = [
                    'PHP',
                    'JavaScript',
                    'Python',
                    'Java',
                    'C#',
                    'React',
                    'Vue.js',
                    'Laravel'
                ];
            } elseif ($pregunta->tipo === 'lista_desplegable') {
                $respuestas = [
                    'Desarrollo',
                    'Diseño',
                    'Marketing',
                    'Ventas',
                    'Recursos Humanos',
                    'Finanzas'
                ];
            } elseif ($pregunta->tipo === 'escala_lineal') {
                $respuestas = [
                    'Muy Insatisfecho',
                    'Insatisfecho',
                    'Neutral',
                    'Satisfecho',
                    'Muy Satisfecho'
                ];
            } else {
                continue; // No necesita respuestas predefinidas
            }

            foreach ($respuestas as $index => $texto) {
                Respuesta::create([
                    'pregunta_id' => $pregunta->id,
                    'texto' => $texto,
                    'orden' => $index + 1
                ]);
            }

            $this->line("   ✅ Respuestas para '{$pregunta->texto}': " . count($respuestas) . " opciones");
        }

        $this->line('');
    }

    private function configurarLogica()
    {
        $this->info("🔀 CONFIGURANDO LÓGICA CONDICIONAL:");

        $preguntasConRespuestas = $this->encuesta->preguntas()->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion'])->get();

        if ($preguntasConRespuestas->isEmpty()) {
            $this->line("   ℹ️  No hay preguntas que requieran lógica condicional");
            return;
        }

        foreach ($preguntasConRespuestas as $pregunta) {
            $respuestas = $pregunta->respuestas;

            if ($respuestas->count() > 0) {
                // Crear lógica para la primera respuesta
                $primeraRespuesta = $respuestas->first();

                Logica::create([
                    'encuesta_id' => $this->encuesta->id,
                    'pregunta_origen_id' => $pregunta->id,
                    'respuesta_origen_id' => $primeraRespuesta->id,
                    'pregunta_destino_id' => $pregunta->id + 1, // Siguiente pregunta
                    'accion' => 'saltar_a'
                ]);

                $this->line("   ✅ Lógica creada para '{$pregunta->texto}'");
            }
        }

        $this->line('');
    }

    private function configurarEnvioMasivo()
    {
        $this->info("📧 CONFIGURANDO ENVÍO MASIVO:");

        // Actualizar encuesta para envío masivo
        $this->encuesta->update([
            'enviar_por_correo' => true,
            'envio_masivo_activado' => true,
            'validacion_completada' => true,
            'plantilla_correo' => 'Hola, te invitamos a participar en nuestra encuesta.',
            'asunto_correo' => 'Encuesta de Prueba - Tester Automático'
        ]);

        // Crear usuarios de prueba
        $usuarios = [];
        for ($i = 1; $i <= $this->cantidadUsuarios; $i++) {
            $usuarios[] = [
                'nombre' => "Usuario Prueba {$i}",
                'email' => $this->emailTest, // Todos van al mismo email
                'cargo' => "Cargo Prueba {$i}",
                'empresa' => $this->empresa->nombre
            ];
        }

        // Simular envío de correos
        foreach ($usuarios as $index => $usuario) {
            // Aquí normalmente se enviaría el correo
            // Por ahora solo registramos
            $this->line("   📨 Email {$index + 1}: {$usuario['email']} - {$usuario['nombre']}");
        }

        $this->line("   ✅ Configuración de envío completada");
        $this->line("   📊 Total emails a enviar: {$this->cantidadUsuarios}");
        $this->line('');
    }

    private function verificarResultados()
    {
        $this->info("🔍 VERIFICANDO RESULTADOS:");

        $this->encuesta->refresh();

        $this->line("   📝 Encuesta:");
        $this->line("     - ID: {$this->encuesta->id}");
        $this->line("     - Título: {$this->encuesta->titulo}");
        $this->line("     - Estado: {$this->encuesta->estado}");
        $this->line("     - Preguntas: {$this->encuesta->preguntas()->count()}");
        $this->line("     - Envío masivo: " . ($this->encuesta->envio_masivo_activado ? 'Activado' : 'Desactivado'));

        $this->line("   ❓ Preguntas creadas:");
        foreach ($this->encuesta->preguntas as $pregunta) {
            $respuestas = $pregunta->respuestas()->count();
            $this->line("     - {$pregunta->texto} ({$pregunta->tipo}) - {$respuestas} respuestas");
        }

        $this->line("   🔀 Lógica condicional:");
        $logicaCount = Logica::where('encuesta_id', $this->encuesta->id)->count();
        $this->line("     - Reglas creadas: {$logicaCount}");

        $this->line('');
    }

    private function mostrarResumen()
    {
        $this->info("🎯 RESUMEN DEL TEST:");
        $this->line('');
        $this->line("✅ FLUJO COMPLETADO EXITOSAMENTE:");
        $this->line("   📝 Encuesta creada y configurada");
        $this->line("   ❓ Preguntas de todos los tipos agregadas");
        $this->line("   📋 Respuestas configuradas");
        $this->line("   🔀 Lógica condicional establecida");
        $this->line("   📧 Envío masivo configurado");
        $this->line('');
        $this->line("📊 ESTADÍSTICAS:");
        $this->line("   - Encuesta ID: {$this->encuesta->id}");
        $this->line("   - Preguntas: {$this->encuesta->preguntas()->count()}");
        $this->line("   - Respuestas totales: " . Respuesta::whereIn('pregunta_id', $this->encuesta->preguntas->pluck('id'))->count());
        $this->line("   - Reglas de lógica: " . Logica::where('encuesta_id', $this->encuesta->id)->count());
        $this->line("   - Emails a enviar: {$this->cantidadUsuarios}");
        $this->line('');
        $this->line("🔗 ACCESOS:");
        $this->line("   - Ver encuesta: " . route('encuestas.show', $this->encuesta->id));
        $this->line("   - Dashboard: " . route('encuestas.seguimiento.dashboard', $this->encuesta->id));
        $this->line('');
        $this->info("🎉 ¡Test completado exitosamente!");
    }
}
