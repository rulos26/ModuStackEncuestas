<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $fillable = ['pregunta_id', 'texto', 'orden'];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }
}
