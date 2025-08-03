<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublicarEncuestaHelpCommand extends Command
{
    protected $signature = 'encuesta:ayuda';
    protected $description = 'Mostrar ayuda para el comando de publicación de encuestas';

    public function handle()
    {
        $this->info('=== AYUDA: PUBLICAR ENCUESTA Y GENERAR ENLACE ===');
        $this->line('');

        $this->info('📋 DESCRIPCIÓN:');
        $this->info('Este comando publica una encuesta y genera un enlace de acceso público.');
        $this->line('');

        $this->info('🚀 USO:');
        $this->info('php artisan encuesta:publicar-y-generar-enlace {encuesta_id}');
        $this->line('');

        $this->info('📝 EJEMPLOS:');
        $this->info('  php artisan encuesta:publicar-y-generar-enlace 1');
        $this->info('  php artisan encuesta:publicar-y-generar-enlace 5');
        $this->line('');

        $this->info('✅ FUNCIONALIDADES:');
        $this->info('  • Verifica que la encuesta esté en estado "borrador"');
        $this->info('  • Valida que tenga preguntas configuradas');
        $this->info('  • Cambia el estado a "publicada"');
        $this->info('  • Genera un token de acceso único');
        $this->info('  • Crea un enlace público para la encuesta');
        $this->info('  • Muestra información del enlace generado');
        $this->line('');

        $this->info('🔧 REQUISITOS:');
        $this->info('  • Encuesta debe existir en la base de datos');
        $this->info('  • Encuesta debe tener preguntas configuradas');
        $this->info('  • Conexión a base de datos funcionando');
        $this->line('');

        $this->info('📊 ESTADOS DE ENCUESTA:');
        $this->info('  • borrador: Encuesta en desarrollo');
        $this->info('  • publicada: Encuesta disponible públicamente');
        $this->info('  • enviada: Encuesta enviada por correo');
        $this->line('');

        $this->info('🔗 ENLACE GENERADO:');
        $this->info('  Formato: https://tudominio.com/publica/{slug}?token={token}');
        $this->info('  Ejemplo: https://tudominio.com/publica/mi-encuesta?token=abc123...');
        $this->line('');

        $this->info('⏰ VALIDEZ DEL TOKEN:');
        $this->info('  • Duración: 7 días por defecto');
        $this->info('  • Uso: Una vez por token');
        $this->info('  • Renovación: Se puede generar un nuevo token');
        $this->line('');

        $this->info('📧 ENVÍO DE CORREOS:');
        $this->info('  • Si la encuesta tiene envío por correo habilitado');
        $this->info('  • Se puede usar el dashboard de seguimiento');
        $this->info('  • URL: /encuestas/{id}/seguimiento');
        $this->line('');

        $this->info('🔍 VERIFICACIÓN:');
        $this->info('  • El comando muestra información detallada');
        $this->info('  • Incluye el enlace completo generado');
        $this->info('  • Muestra el token de acceso');
        $this->info('  • Indica la fecha de expiración');
        $this->line('');

        $this->info('❌ POSIBLES ERRORES:');
        $this->info('  • Encuesta no encontrada');
        $this->info('  • Sin preguntas configuradas');
        $this->info('  • Error de conexión a base de datos');
        $this->info('  • Permisos insuficientes');
        $this->line('');

        $this->info('💡 CONSEJOS:');
        $this->info('  • Verifica la configuración de base de datos en .env');
        $this->info('  • Asegúrate de que la encuesta tenga preguntas');
        $this->info('  • Prueba el enlace generado en un navegador');
        $this->info('  • Guarda el token para uso posterior');
        $this->line('');

        $this->info('🎯 COMANDOS RELACIONADOS:');
        $this->info('  • php artisan encuesta:listar - Listar encuestas');
        $this->info('  • php artisan encuesta:estado - Ver estado de encuestas');
        $this->info('  • php artisan encuesta:limpiar-tokens - Limpiar tokens expirados');
        $this->line('');

        $this->info('📞 SOPORTE:');
        $this->info('Si tienes problemas, verifica:');
        $this->info('  1. Configuración de base de datos');
        $this->info('  2. Permisos de usuario');
        $this->info('  3. Estado de la encuesta');
        $this->info('  4. Logs del sistema');
        $this->line('');

        $this->info('🎉 ¡COMANDO LISTO PARA USAR!');
        $this->info('Ejecuta: php artisan encuesta:publicar-y-generar-enlace {id}');
    }
}
