<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $guarded = ['id'];

    protected $table = 'positions';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
