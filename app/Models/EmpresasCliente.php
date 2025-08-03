<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresasCliente extends Model
{
    protected $table = 'empresas_clientes';
    protected $fillable = [
        'nombre', 'nit', 'telefono', 'correo_electronico', 'direccion', 'contacto'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'empresa_id');
    }
}
