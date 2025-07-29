<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class EjecutarMigracionSentMails extends Command
{
    protected $signature = 'migracion:sent-mails {--debug}';
    protected $description = 'Ejecuta la migración para agregar status a sent_mails';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔧 EJECUTANDO MIGRACIÓN SENT_MAILS");
        $this->line('');

        try {
            // 1. Verificar si la tabla existe
            $this->verificarTabla();

            // 2. Verificar columnas actuales
            $this->verificarColumnas();

            // 3. Agregar columnas si no existen
            $this->agregarColumnas();

            // 4. Verificar resultado
            $this->verificarResultado();

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante la migración: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function verificarTabla()
    {
        $this->info("📋 VERIFICANDO TABLA:");

        if (Schema::hasTable('sent_mails')) {
            $this->line("   ✅ Tabla sent_mails: Existe");

            $count = DB::table('sent_mails')->count();
            $this->line("   - Registros: {$count}");
        } else {
            $this->error("   ❌ Tabla sent_mails: No existe");
        }
        $this->line('');
    }

    private function verificarColumnas()
    {
        $this->info("🔍 VERIFICANDO COLUMNAS:");

        $columnas = ['id', 'to', 'subject', 'body', 'status', 'error_message', 'attachments', 'sent_by', 'encuesta_id', 'created_at', 'updated_at'];

        foreach ($columnas as $columna) {
            if (Schema::hasColumn('sent_mails', $columna)) {
                $this->line("   ✅ {$columna}: Existe");
            } else {
                $this->line("   ❌ {$columna}: No existe");
            }
        }
        $this->line('');
    }

    private function agregarColumnas()
    {
        $this->info("🔧 AGREGANDO COLUMNAS:");

        // Verificar si status existe
        if (!Schema::hasColumn('sent_mails', 'status')) {
            DB::statement("ALTER TABLE sent_mails ADD COLUMN status ENUM('sent', 'error', 'pending') DEFAULT 'sent' AFTER body");
            $this->line("   ✅ Columna status: Agregada");
        } else {
            $this->line("   ℹ️  Columna status: Ya existe");
        }

        // Verificar si error_message existe
        if (!Schema::hasColumn('sent_mails', 'error_message')) {
            DB::statement("ALTER TABLE sent_mails ADD COLUMN error_message TEXT NULL AFTER status");
            $this->line("   ✅ Columna error_message: Agregada");
        } else {
            $this->line("   ℹ️  Columna error_message: Ya existe");
        }

        $this->line('');
    }

    private function verificarResultado()
    {
        $this->info("✅ VERIFICANDO RESULTADO:");

        $columnas = ['status', 'error_message'];

        foreach ($columnas as $columna) {
            if (Schema::hasColumn('sent_mails', $columna)) {
                $this->line("   ✅ {$columna}: OK");
            } else {
                $this->error("   ❌ {$columna}: FALLA");
            }
        }

        // Verificar registros existentes
        $count = DB::table('sent_mails')->count();
        if ($count > 0) {
            $this->line("   ℹ️  Actualizando registros existentes...");

            // Actualizar registros existentes con status 'sent'
            DB::table('sent_mails')->whereNull('status')->update(['status' => 'sent']);

            $this->line("   ✅ Registros actualizados");
        }

        $this->line('');
        $this->info("🎉 ¡Migración completada exitosamente!");
    }
}
