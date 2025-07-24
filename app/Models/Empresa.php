<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $fillable = [
        'nombre_legal', 'nit', 'representante_legal', 'telefono', 'email', 'direccion',
        'pais_id', 'departamento_id', 'municipio_id', 'mision', 'vision', 'descripcion', 'fecha_creacion'
    ];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }
}
