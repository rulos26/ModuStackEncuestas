<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logica extends Model
{
    protected $fillable = ['pregunta_id', 'respuesta_id', 'siguiente_pregunta_id', 'finalizar'];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function respuesta()
    {
        return $this->belongsTo(Respuesta::class);
    }

    public function siguientePregunta()
    {
        return $this->belongsTo(Pregunta::class, 'siguiente_pregunta_id');
    }
}
