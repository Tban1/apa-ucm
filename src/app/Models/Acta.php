<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acta extends Model
{
    use HasUuids;

    protected $fillable = [
        'periodo_id', 'facultad_id', 'archivo',
        'generada_por', 'fecha', 'tipo',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }

    public function generadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generada_por');
    }
}