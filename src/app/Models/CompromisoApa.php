<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompromisoApa extends Model
{
    use HasUuids;

    public const AREAS = [
        'docencia'       => 'Docencia',
        'investigacion'  => 'Investigación',
        'extension'      => 'Extensión',
        'administracion' => 'Administración',
        'otras'          => 'Otras',
    ];

    protected $table = 'compromisos_apa';

    protected $fillable = [
        'academico_id', 'periodo_id', 'area', 'porcentaje', 'semestre_negociacion',
    ];

    public function academico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academico_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
}
