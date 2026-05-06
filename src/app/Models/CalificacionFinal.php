<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionFinal extends Model
{
    use HasUuids;

    protected $table = 'calificaciones_finales';

    protected $fillable = [
        'nomina_id', 'puntaje_total', 'calificacion',
        'determinada_por', 'fecha', 'observacion', 'es_apelacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha'        => 'date',
            'es_apelacion' => 'boolean',
        ];
    }

    /**
     * Retorna el label legible de la calificación.
     */
    public function calificacionLabel(): string
    {
        return match($this->calificacion) {
            'muy_bueno'  => 'Muy Bueno',
            'bueno'      => 'Bueno',
            'aceptable'  => 'Aceptable',
            'deficiente' => 'Deficiente',
            default      => $this->calificacion,
        };
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function determinadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'determinada_por');
    }
}