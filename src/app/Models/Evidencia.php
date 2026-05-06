<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidencia extends Model
{
    use HasUuids;

    protected $fillable = [
        'nomina_id', 'categoria_id', 'nombre_archivo',
        'ruta', 'tamano', 'mime_type', 'subido_por',
        'es_apelacion', 'descripcion',
    ];

    protected function casts(): array
    {
        return ['es_apelacion' => 'boolean'];
    }

    // Tamaño formateado (ej: "1.2 MB")
    public function tamanoFormateado(): string
    {
        $bytes = $this->tamano;
        if ($bytes < 1024)        return "{$bytes} B";
        if ($bytes < 1048576)     return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaApa::class, 'categoria_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}