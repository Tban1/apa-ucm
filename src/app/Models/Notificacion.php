<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasUuids;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id', 'tipo', 'titulo',
        'mensaje', 'leida', 'url',
    ];

    protected function casts(): array
    {
        return ['leida' => 'boolean'];
    }

    public function marcarLeida(): void
    {
        $this->update(['leida' => true]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }
}