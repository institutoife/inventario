<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalStatus extends Model
{
    protected $guarded = ['id'];

    protected $table = 'physical_statuses';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
