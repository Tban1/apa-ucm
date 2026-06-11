<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificacionCcda extends Model
{
    use HasUuids;

    protected $table = 'verificaciones_ccda';

    protected $fillable = [
        'periodo_id', 'facultad_id', 'verificado_por',
        'doc_fisica_archivada', 'notas_comunicadas',
        'observaciones', 'verificado_en',
    ];

    protected function casts(): array
    {
        return [
            'doc_fisica_archivada' => 'boolean',
            'notas_comunicadas'    => 'boolean',
            'verificado_en'        => 'datetime',
        ];
    }

    public function estaVerificada(): bool
    {
        return $this->verificado_en !== null;
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }
}
