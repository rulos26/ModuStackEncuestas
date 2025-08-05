<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerificarSweetAlert2 extends Command
{
    protected $signature = 'verificar:sweetalert2';
    protected $description = 'Verificar que SweetAlert2 esté disponible y funcionando';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO SWEETALERT2');
        $this->line('');

        // Verificar si SweetAlert2 está incluido en las vistas
        $this->info('📋 VERIFICACIÓN DE INCLUSIÓN:');

        // Verificar layout principal
        $layoutPath = resource_path('views/layouts/app.blade.php');
        if (file_exists($layoutPath)) {
            $layoutContent = file_get_contents($layoutPath);
            if (strpos($layoutContent, 'sweetalert2') !== false) {
                $this->info('   ✅ SweetAlert2 incluido en layouts/app.blade.php');
            } else {
                $this->warn('   ⚠️  SweetAlert2 NO incluido en layouts/app.blade.php');
            }
        }

        // Verificar vista de configuración de envío
        $resumenPath = resource_path('views/configuracion_envio/resumen.blade.php');
        if (file_exists($resumenPath)) {
            $resumenContent = file_get_contents($resumenPath);
            if (strpos($resumenContent, 'sweetalert2') !== false) {
                $this->info('   ✅ SweetAlert2 incluido en configuracion_envio/resumen.blade.php');
            } else {
                $this->warn('   ⚠️  SweetAlert2 NO incluido en configuracion_envio/resumen.blade.php');
            }
        }

        $this->line('');

        // Verificar funciones de notificación
        $this->info('🔧 VERIFICACIÓN DE FUNCIONES:');

        if (strpos($resumenContent, 'showSuccess') !== false) {
            $this->info('   ✅ Función showSuccess definida');
        } else {
            $this->error('   ❌ Función showSuccess NO definida');
        }

        if (strpos($resumenContent, 'showError') !== false) {
            $this->info('   ✅ Función showError definida');
        } else {
            $this->error('   ❌ Función showError NO definida');
        }

        if (strpos($resumenContent, 'showWarning') !== false) {
            $this->info('   ✅ Función showWarning definida');
        } else {
            $this->error('   ❌ Función showWarning NO definida');
        }

        $this->line('');

        // Verificar verificaciones de Swal
        $this->info('🛡️ VERIFICACIÓN DE SEGURIDAD:');

        if (strpos($resumenContent, 'typeof Swal !== \'undefined\'') !== false) {
            $this->info('   ✅ Verificación typeof Swal implementada');
        } else {
            $this->warn('   ⚠️  Verificación typeof Swal NO implementada');
        }

        $this->line('');

        // Recomendaciones
        $this->info('💡 RECOMENDACIONES:');

        if (strpos($resumenContent, 'sweetalert2') === false) {
            $this->warn('   ➡️  Agregar SweetAlert2 a la vista resumen.blade.php');
            $this->line('      @push(\'js\')');
            $this->line('      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>');
            $this->line('      @endpush');
        }

        if (strpos($resumenContent, 'typeof Swal !== \'undefined\'') === false) {
            $this->warn('   ➡️  Agregar verificaciones de Swal en las funciones');
            $this->line('      if (typeof Swal !== \'undefined\') {');
            $this->line('          Swal.fire({...});');
            $this->line('      } else {');
            $this->line('          alert(\'Mensaje\');');
            $this->line('      }');
        }

        $this->line('');
        $this->info('✅ Verificación completada');

        return 0;
    }
}
