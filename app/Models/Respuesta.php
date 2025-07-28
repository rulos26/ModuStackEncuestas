<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Respuesta extends Model
{
    protected $fillable = ['pregunta_id', 'texto', 'orden'];

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function logica(): HasMany
    {
        return $this->hasMany(Logica::class);
    }
}
