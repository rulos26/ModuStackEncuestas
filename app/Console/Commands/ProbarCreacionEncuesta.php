<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\Empresa;
use App\Models\User;
use App\Http\Requests\EncuestaRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProbarCreacionEncuesta extends Command
{
    protected $signature = 'encuestas:probar-creacion {--empresa_id=} {--user_id=}';
    protected $description = 'Prueba la creación de encuestas';

    public function handle()
    {
        $this->info('🧪 PROBANDO CREACIÓN DE ENCUESTAS');
        $this->info('==================================');

        try {
            // Verificar datos necesarios
            $this->verificarDatosNecesarios();

            // Crear encuesta de prueba
            $this->crearEncuestaPrueba();

            $this->info("\n🎉 PRUEBA COMPLETADA EXITOSAMENTE");
            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE LA PRUEBA:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function verificarDatosNecesarios()
    {
        $this->info("\n📋 VERIFICANDO DATOS NECESARIOS:");

        // Verificar empresas
        $empresas = Empresa::count();
        if ($empresas === 0) {
            throw new \Exception('No hay empresas disponibles. Crea al menos una empresa primero.');
        }
        $this->info("   ✅ Empresas disponibles: {$empresas}");

        // Verificar usuarios
        $usuarios = User::count();
        if ($usuarios === 0) {
            throw new \Exception('No hay usuarios disponibles. Crea al menos un usuario primero.');
        }
        $this->info("   ✅ Usuarios disponibles: {$usuarios}");

        // Mostrar empresas disponibles
        $empresasList = Empresa::select('id', 'nombre_legal')->get();
        $this->info("   📊 Empresas disponibles:");
        foreach ($empresasList as $empresa) {
            $this->info("      • ID: {$empresa->id} - {$empresa->nombre_legal}");
        }

        // Mostrar usuarios disponibles
        $usuariosList = User::select('id', 'name', 'email')->get();
        $this->info("   👥 Usuarios disponibles:");
        foreach ($usuariosList as $usuario) {
            $this->info("      • ID: {$usuario->id} - {$usuario->name} ({$usuario->email})");
        }
    }

    private function crearEncuestaPrueba()
    {
        $this->info("\n🔧 CREANDO ENCUESTA DE PRUEBA:");

        // Obtener empresa y usuario
        $empresa = Empresa::first();
        $usuario = User::first();

        if (!$empresa) {
            throw new \Exception('No se encontró ninguna empresa');
        }

        if (!$usuario) {
            throw new \Exception('No se encontró ningún usuario');
        }

        $this->info("   🏢 Usando empresa: {$empresa->nombre_legal} (ID: {$empresa->id})");
        $this->info("   👤 Usando usuario: {$usuario->name} (ID: {$usuario->id})");

        // Simular datos de request
        $datosPrueba = [
            'titulo' => 'Encuesta de Prueba - ' . now()->format('Y-m-d H:i:s'),
            'empresa_id' => $empresa->id,
            'numero_encuestas' => 10,
            'fecha_inicio' => now()->addDay()->toDateString(),
            'fecha_fin' => now()->addDays(7)->toDateString(),
            'enviar_por_correo' => true,
            'plantilla_correo' => 'Plantilla de prueba',
            'asunto_correo' => 'Asunto de prueba',
            'envio_masivo_activado' => false,
            'estado' => 'borrador',
            'habilitada' => true
        ];

        $this->info("   📝 Datos de prueba preparados");

        // Validar datos
        $this->validarDatos($datosPrueba);

        // Crear encuesta
        DB::beginTransaction();

        try {
            $encuesta = Encuesta::create([
                'titulo' => $datosPrueba['titulo'],
                'empresa_id' => $datosPrueba['empresa_id'],
                'numero_encuestas' => $datosPrueba['numero_encuestas'],
                'fecha_inicio' => $datosPrueba['fecha_inicio'],
                'fecha_fin' => $datosPrueba['fecha_fin'],
                'enviar_por_correo' => $datosPrueba['enviar_por_correo'],
                'plantilla_correo' => $datosPrueba['plantilla_correo'],
                'asunto_correo' => $datosPrueba['asunto_correo'],
                'envio_masivo_activado' => $datosPrueba['envio_masivo_activado'],
                'estado' => $datosPrueba['estado'],
                'habilitada' => $datosPrueba['habilitada'],
                'user_id' => $usuario->id
            ]);

            DB::commit();

            $this->info("   ✅ Encuesta creada exitosamente");
            $this->info("   🆔 ID de la encuesta: {$encuesta->id}");
            $this->info("   📊 Título: {$encuesta->titulo}");
            $this->info("   🏢 Empresa: {$encuesta->empresa->nombre_legal}");
            $this->info("   👤 Usuario: {$encuesta->user->name}");
            $this->info("   📅 Estado: {$encuesta->estado}");
            $this->info("   🔗 Slug: {$encuesta->slug}");

            // Verificar métodos del modelo
            $this->verificarMetodosModelo($encuesta);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error creando encuesta: ' . $e->getMessage());
        }
    }

    private function validarDatos($datos)
    {
        $this->info("   ✅ Validando datos...");

        // Validaciones básicas
        if (empty($datos['titulo'])) {
            throw new \Exception('El título es obligatorio');
        }

        if (strlen($datos['titulo']) < 3) {
            throw new \Exception('El título debe tener al menos 3 caracteres');
        }

        if (empty($datos['empresa_id'])) {
            throw new \Exception('La empresa es obligatoria');
        }

        if (!Empresa::find($datos['empresa_id'])) {
            throw new \Exception('La empresa seleccionada no existe');
        }

        $this->info("   ✅ Datos válidos");
    }

    private function verificarMetodosModelo($encuesta)
    {
        $this->info("\n🔧 VERIFICANDO MÉTODOS DEL MODELO:");

        // Verificar método estaDisponible
        if (method_exists($encuesta, 'estaDisponible')) {
            $disponible = $encuesta->estaDisponible();
            $this->info("   ✅ estaDisponible(): " . ($disponible ? 'Sí' : 'No'));
        } else {
            $this->error("   ❌ Método estaDisponible() no existe");
        }

        // Verificar método puedeAvanzarA
        if (method_exists($encuesta, 'puedeAvanzarA')) {
            $puedePreguntas = $encuesta->puedeAvanzarA('preguntas');
            $this->info("   ✅ puedeAvanzarA('preguntas'): " . ($puedePreguntas ? 'Sí' : 'No'));
        } else {
            $this->error("   ❌ Método puedeAvanzarA() no existe");
        }

        // Verificar método obtenerProgresoConfiguracion
        if (method_exists($encuesta, 'obtenerProgresoConfiguracion')) {
            $progreso = $encuesta->obtenerProgresoConfiguracion();
            $this->info("   ✅ obtenerProgresoConfiguracion(): " . $progreso['completados'] . "/" . $progreso['total'] . " pasos completados");
        } else {
            $this->error("   ❌ Método obtenerProgresoConfiguracion() no existe");
        }

        // Verificar relaciones
        $this->info("   🔗 Verificando relaciones:");
        $this->info("      • empresa: " . ($encuesta->empresa ? '✅' : '❌'));
        $this->info("      • user: " . ($encuesta->user ? '✅' : '❌'));
        $this->info("      • preguntas: " . $encuesta->preguntas->count() . " preguntas");
    }
}
