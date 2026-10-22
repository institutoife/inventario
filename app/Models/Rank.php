<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $guarded = ['id'];

    protected $table = 'ranks';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
