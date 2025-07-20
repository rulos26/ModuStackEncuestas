<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    protected $table = 'paises';
    protected $fillable = [
        'name', 'iso_name', 'alfa2', 'alfa3', 'numerico'
    ];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'pais_id');
    }
}
