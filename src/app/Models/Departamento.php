<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    use HasUuids;

    protected $fillable = ['nombre', 'facultad_id'];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}