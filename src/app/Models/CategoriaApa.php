<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaApa extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'categorias_apa';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'orden'];

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class, 'categoria_id');
    }
}