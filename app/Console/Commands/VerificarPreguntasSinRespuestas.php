<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pregunta;
use App\Models\Encuesta;

class VerificarPreguntasSinRespuestas extends Command
{
    protected $signature = 'wizard:verificar-preguntas';
    protected $description = 'Verifica las preguntas que necesitan respuestas configuradas';

    public function handle()
    {
        $this->info('Verificando preguntas que necesitan respuestas configuradas...');

        // Obtener todas las preguntas configurables
        $preguntas = Pregunta::with(['respuestas', 'encuesta'])
            ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
            ->get();

        $this->info("Total de preguntas configurables encontradas: {$preguntas->count()}");

        $preguntasSinRespuestas = $preguntas->filter(function ($pregunta) {
            return $pregunta->respuestas->isEmpty();
        });

        $this->info("Preguntas sin respuestas configuradas: {$preguntasSinRespuestas->count()}");

        if ($preguntasSinRespuestas->count() > 0) {
            $this->table(
                ['ID', 'Encuesta ID', 'Tipo', 'Texto', 'Respuestas'],
                $preguntasSinRespuestas->map(function ($pregunta) {
                    return [
                        $pregunta->id,
                        $pregunta->encuesta_id,
                        $pregunta->tipo,
                        substr($pregunta->texto, 0, 50) . '...',
                        $pregunta->respuestas->count()
                    ];
                })->toArray()
            );
        } else {
            $this->warn('No se encontraron preguntas sin respuestas configuradas.');
        }

        // Verificar encuestas que tienen preguntas sin respuestas
        $encuestas = Encuesta::with(['preguntas.respuestas'])
            ->where('estado', '!=', 'borrador')
            ->get()
            ->filter(function ($encuesta) {
                return $encuesta->preguntas->some(function ($pregunta) {
                    return $pregunta->respuestas->isEmpty() &&
                           in_array($pregunta->tipo, ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple']);
                });
            });

        $this->info("Encuestas que necesitan configuración: {$encuestas->count()}");

        if ($encuestas->count() > 0) {
            $this->table(
                ['ID', 'Título', 'Preguntas Sin Respuestas'],
                $encuestas->map(function ($encuesta) {
                    $preguntasSinRespuestas = $encuesta->preguntas->filter(function ($pregunta) {
                        return $pregunta->respuestas->isEmpty() &&
                               in_array($pregunta->tipo, ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple']);
                    });

                    return [
                        $encuesta->id,
                        $encuesta->titulo,
                        $preguntasSinRespuestas->count()
                    ];
                })->toArray()
            );
        }

        return 0;
    }
}
