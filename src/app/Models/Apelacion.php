<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apelacion extends Model
{
    use HasUuids;

    protected $table = 'apelaciones';

    protected $fillable = [
        'nomina_id', 'motivo', 'estado',
        'fecha_solicitud', 'fecha_resolucion', 'resolucion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud'   => 'date',
            'fecha_resolucion'  => 'date',
        ];
    }

    public function estaResuelta(): bool
    {
        return in_array($this->estado, ['resuelta', 'rechazada']);
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }
}