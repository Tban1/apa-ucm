<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemestreAcademico extends Model
{
    use HasUuids;

    protected $table = 'semestres_academicos';

    protected $fillable = [
        'periodo_id',
        'numero',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cierre' => 'date',
            'numero' => 'integer',
        ];
    }

    public function estaCerrado(): bool
    {
        return today()->isAfter($this->fecha_cierre);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
}
