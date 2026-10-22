<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeStatus extends Model
{
    protected $guarded = ['id'];

    protected $table = 'administrative_statuses';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
