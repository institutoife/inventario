<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('La auditoría no se modifica.'));
        static::deleting(fn () => throw new \LogicException('La auditoría no se elimina.'));
    }
}
