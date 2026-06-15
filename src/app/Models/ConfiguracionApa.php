<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionApa extends Model
{
    protected $table = 'configuraciones_apa';

    protected $primaryKey = 'clave';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    public static function get(string $clave, mixed $default = null): mixed
    {
        return static::find($clave)?->valor ?? $default;
    }
}
