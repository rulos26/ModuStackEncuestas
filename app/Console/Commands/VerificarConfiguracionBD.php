<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class VerificarConfiguracionBD extends Command
{
    protected $signature = 'bd:verificar-configuracion {--corregir} {--debug}';
    protected $description = 'Verifica y corrige la configuración de la base de datos';

    public function handle()
    {
        $this->info('🔧 VERIFICACIÓN DE CONFIGURACIÓN DE BASE DE DATOS');
        $this->info('==============================================');

        try {
            // Verificar configuración actual
            $this->verificarConfiguracionActual();

            // Verificar conexión
            $this->verificarConexion();

            // Corregir si se solicita
            if ($this->option('corregir')) {
                $this->corregirConfiguracion();
            }

            // Mostrar recomendaciones
            $this->mostrarRecomendaciones();

        } catch (\Exception $e) {
            $this->error('❌ Error durante la verificación: ' . $e->getMessage());
            if ($this->option('debug')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    private function verificarConfiguracionActual()
    {
        $this->info("\n📊 CONFIGURACIÓN ACTUAL:");

        $configuracion = [
            'DB_CONNECTION' => env('DB_CONNECTION', 'mysql'),
            'DB_HOST' => env('DB_HOST', '127.0.0.1'),
            'DB_PORT' => env('DB_PORT', '3306'),
            'DB_DATABASE' => env('DB_DATABASE', 'laravel'),
            'DB_USERNAME' => env('DB_USERNAME', 'root'),
            'DB_PASSWORD' => env('DB_PASSWORD', ''),
        ];

        foreach ($configuracion as $clave => $valor) {
            if ($clave === 'DB_PASSWORD') {
                $valor = $valor ? '***CONTRASEÑA***' : '(vacía)';
            }
            $this->info("   {$clave}: {$valor}");
        }

        // Verificar si está configurado para servidor remoto
        $host = env('DB_HOST', '127.0.0.1');
        if ($host !== '127.0.0.1' && $host !== 'localhost') {
            $this->warn("   ⚠️  HOST configurado para servidor remoto: {$host}");
            $this->warn("   💡 Para desarrollo local, debe ser: 127.0.0.1 o localhost");
        } else {
            $this->info("   ✅ HOST configurado para desarrollo local");
        }

        // Verificar usuario
        $username = env('DB_USERNAME', 'root');
        if (strpos($username, 'u494150416_') === 0) {
            $this->warn("   ⚠️  USUARIO configurado para servidor remoto: {$username}");
            $this->warn("   💡 Para desarrollo local, debe ser: root");
        } else {
            $this->info("   ✅ USUARIO configurado para desarrollo local");
        }
    }

    private function verificarConexion()
    {
        $this->info("\n🔌 VERIFICANDO CONEXIÓN:");

        try {
            DB::connection()->getPdo();
            $this->info("   ✅ Conexión exitosa");
            $this->info("   📍 Base de datos: " . DB::connection()->getDatabaseName());
            $this->info("   🖥️  Servidor: " . DB::connection()->getConfig('host'));

        } catch (\Exception $e) {
            $this->error("   ❌ Error de conexión: " . $e->getMessage());

            // Analizar el error
            if (strpos($e->getMessage(), '2002') !== false) {
                $this->warn("   💡 Error 2002: Problema de conexión al servidor");
                $this->warn("   🔧 Soluciones:");
                $this->warn("      - Verifica que MySQL esté ejecutándose en XAMPP");
                $this->warn("      - Verifica que el puerto 3306 esté disponible");
                $this->warn("      - Verifica la configuración en .env");
            } elseif (strpos($e->getMessage(), '1045') !== false) {
                $this->warn("   💡 Error 1045: Credenciales incorrectas");
                $this->warn("   🔧 Soluciones:");
                $this->warn("      - Verifica usuario y contraseña en .env");
                $this->warn("      - Para XAMPP, usuario: root, contraseña: (vacía)");
            } elseif (strpos($e->getMessage(), '1049') !== false) {
                $this->warn("   💡 Error 1049: Base de datos no existe");
                $this->warn("   🔧 Soluciones:");
                $this->warn("      - Crea la base de datos en phpMyAdmin");
                $this->warn("      - Ejecuta: php artisan migrate");
            }
        }
    }

    private function corregirConfiguracion()
    {
        $this->info("\n🔧 CORRIGIENDO CONFIGURACIÓN:");

        try {
            // Leer archivo .env
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                $this->error("   ❌ Archivo .env no encontrado");
                $this->info("   💡 Copia .env.example a .env");
                return;
            }

            $envContent = file_get_contents($envPath);
            $this->info("   ✅ Archivo .env encontrado");

            // Configuración para desarrollo local
            $configuracionLocal = [
                'DB_CONNECTION=mysql',
                'DB_HOST=127.0.0.1',
                'DB_PORT=3306',
                'DB_DATABASE=modustack_encuestas',
                'DB_USERNAME=root',
                'DB_PASSWORD=',
            ];

            $cambios = 0;
            foreach ($configuracionLocal as $config) {
                list($clave, $valor) = explode('=', $config, 2);

                // Buscar línea existente
                $pattern = "/^{$clave}=.*$/m";
                if (preg_match($pattern, $envContent)) {
                    // Reemplazar línea existente
                    $envContent = preg_replace($pattern, $config, $envContent);
                    $this->info("   🔄 Actualizado: {$clave}");
                    $cambios++;
                } else {
                    // Agregar nueva línea
                    $envContent .= "\n{$config}";
                    $this->info("   ➕ Agregado: {$clave}");
                    $cambios++;
                }
            }

            if ($cambios > 0) {
                // Guardar archivo
                file_put_contents($envPath, $envContent);
                $this->info("   ✅ Archivo .env actualizado con {$cambios} cambios");

                // Limpiar caché de configuración
                $this->call('config:clear');
                $this->info("   🧹 Caché de configuración limpiada");

                $this->info("   🔄 Verificando nueva configuración...");
                $this->verificarConexion();
            } else {
                $this->info("   ✅ Configuración ya está correcta");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error corrigiendo configuración: " . $e->getMessage());
        }
    }

    private function mostrarRecomendaciones()
    {
        $this->info("\n💡 RECOMENDACIONES:");
        $this->info("   📌 Para desarrollo local con XAMPP:");
        $this->info("      DB_CONNECTION=mysql");
        $this->info("      DB_HOST=127.0.0.1");
        $this->info("      DB_PORT=3306");
        $this->info("      DB_DATABASE=modustack_encuestas");
        $this->info("      DB_USERNAME=root");
        $this->info("      DB_PASSWORD=");
        $this->info("");
        $this->info("   🔧 Para corregir automáticamente:");
        $this->info("      php artisan bd:verificar-configuracion --corregir");
        $this->info("");
        $this->info("   📋 Pasos manuales:");
        $this->info("      1. Abre XAMPP Control Panel");
        $this->info("      2. Inicia MySQL");
        $this->info("      3. Abre phpMyAdmin");
        $this->info("      4. Crea base de datos: modustack_encuestas");
        $this->info("      5. Ejecuta: php artisan migrate");
        $this->info("");
        $this->info("   🧪 Para probar después de corregir:");
        $this->info("      php artisan preguntas:diagnosticar-creacion");
    }
}
