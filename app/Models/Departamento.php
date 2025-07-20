<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $fillable = [
        'nombre', 'pais_id'
    ];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'departamento_id');
    }
}
