<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Encuesta extends Model
{
    protected $table = 'encuestas';
    protected $fillable = [
        'titulo', 'empresa_id', 'numero_encuestas', 'tiempo_disponible', 'enviar_por_correo', 'estado', 'user_id'
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
