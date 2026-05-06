<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionJefatura extends Model
{
    use HasUuids;

    protected $table = 'calificaciones_jefatura';

    protected $fillable = ['nomina_id', 'jefe_id', 'puntaje', 'comentario'];

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }
}