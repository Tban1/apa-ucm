<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluacion extends Model
{
    use HasUuids;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'nomina_id', 'evaluador_id',
        'puntaje_docencia', 'puntaje_investigacion',
        'puntaje_vinculacion', 'puntaje_gestion', 'puntaje_formacion',
        'comentario', 'es_apelacion',
    ];

    protected function casts(): array
    {
        return ['es_apelacion' => 'boolean'];
    }

    public function puntajeTotal(): int
    {
        return $this->puntaje_docencia
             + $this->puntaje_investigacion
             + $this->puntaje_vinculacion
             + $this->puntaje_gestion
             + $this->puntaje_formacion;
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }
}