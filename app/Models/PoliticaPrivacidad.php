<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticaPrivacidad extends Model
{
    protected $table = 'politicas_privacidad';

    protected $fillable = [
        'titulo',
        'contenido',
        'estado',
        'version',
        'fecha_publicacion',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'fecha_publicacion' => 'date',
    ];
}
