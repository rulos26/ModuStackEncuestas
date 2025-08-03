<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'telefono',
        'correo_electronico',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasCliente::class, 'empresa_id');
    }
}
