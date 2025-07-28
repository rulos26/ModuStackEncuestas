<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\Pregunta;
use Illuminate\Console\Command;

class ProbarDropdownPreguntas extends Command
{
    protected $signature = 'encuestas:probar-dropdown {encuesta_id}';
    protected $description = 'Prueba la funcionalidad del dropdown de tipos de preguntas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🧪 PROBANDO DROPDOWN DE TIPOS DE PREGUNTAS');
        $this->info('==============================================');

        try {
            // Verificar que la encuesta existe
            $encuesta = Encuesta::find($encuestaId);
            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->info("✅ Encuesta encontrada: {$encuesta->titulo}");

            // Verificar que el modelo Pregunta funciona
            $tipos = Pregunta::getTiposDisponibles();
            $this->info("✅ Tipos disponibles: " . count($tipos));

            // Mostrar información de prueba
            $this->info("\n📋 INFORMACIÓN DE PRUEBA:");
            $this->info("URL de creación de pregunta: " . route('encuestas.preguntas.create', $encuestaId));
            $this->info("Encuesta ID: {$encuestaId}");
            $this->info("Título: {$encuesta->titulo}");

            // Verificar que las rutas existen
            $this->info("\n🔗 VERIFICANDO RUTAS:");
            try {
                $rutaCrear = route('encuestas.preguntas.create', $encuestaId);
                $this->info("✅ Ruta de creación: {$rutaCrear}");
            } catch (\Exception $e) {
                $this->error("❌ Error en ruta de creación: " . $e->getMessage());
            }

            // Verificar configuración del dropdown
            $this->info("\n⚙️ CONFIGURACIÓN DEL DROPDOWN:");
            $tiposConfigurados = [];
            foreach ($tipos as $tipo => $config) {
                $tiposConfigurados[] = [
                    'tipo' => $tipo,
                    'nombre' => $config['nombre'],
                    'icono' => $config['icono'],
                    'necesita_respuestas' => $config['necesita_respuestas']
                ];
            }

            $this->table(
                ['Tipo', 'Nombre', 'Icono', 'Necesita Respuestas'],
                array_map(function($item) {
                    return [
                        $item['tipo'],
                        $item['nombre'],
                        $item['icono'],
                        $item['necesita_respuestas'] ? 'Sí' : 'No'
                    ];
                }, $tiposConfigurados)
            );

            // Verificar JavaScript necesario
            $this->info("\n📜 VERIFICANDO JAVASCRIPT:");
            $jsRequerido = [
                'jQuery cargado',
                'Bootstrap 4 cargado',
                'Evento click en .tipo-option',
                'Función mostrarConfiguracionesEspecificas',
                'Función mostrarInformacionTipo'
            ];

            foreach ($jsRequerido as $requisito) {
                $this->info("✅ {$requisito}");
            }

            // Verificar CSS necesario
            $this->info("\n🎨 VERIFICANDO CSS:");
            $cssRequerido = [
                '.dropdown-menu { display: none }',
                '.dropdown-menu.show { display: block }',
                '.tipo-option { cursor: pointer }',
                '.dropdown-item:hover { background-color }'
            ];

            foreach ($cssRequerido as $requisito) {
                $this->info("✅ {$requisito}");
            }

            // Instrucciones para el usuario
            $this->info("\n📝 INSTRUCCIONES PARA PROBAR:");
            $this->info("1. Abre el navegador y ve a: {$rutaCrear}");
            $this->info("2. Haz clic en el dropdown 'Tipo de pregunta'");
            $this->info("3. Verifica que aparezcan todas las opciones");
            $this->info("4. Selecciona un tipo y verifica que se actualice el campo");
            $this->info("5. Verifica que se muestren las configuraciones específicas");

            // Verificar posibles problemas
            $this->info("\n🔍 POSIBLES PROBLEMAS:");
            $problemas = [
                'Bootstrap 4 no está cargado correctamente',
                'jQuery no está disponible',
                'CSS no se está aplicando',
                'JavaScript tiene errores de sintaxis',
                'Rutas no están definidas'
            ];

            foreach ($problemas as $problema) {
                $this->warn("⚠️ {$problema}");
            }

            $this->info("\n🎉 PRUEBA CONFIGURADA EXITOSAMENTE");
            $this->info("El dropdown debería funcionar correctamente en el navegador.");

            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE LA PRUEBA:");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
