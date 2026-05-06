<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facultad extends Model
{
    use HasUuids;

    protected $table = 'facultades';

    protected $fillable = ['nombre', 'codigo'];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(Acta::class);
    }
}