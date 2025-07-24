<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Encuesta;
use App\Models\Respuesta;

class Pregunta extends Model
{
    protected $fillable = [
        'encuesta_id', 'texto', 'tipo', 'orden', 'obligatoria',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class);
    }
}
