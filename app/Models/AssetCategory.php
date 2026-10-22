<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $guarded = ['id'];

    protected $table = 'asset_categories';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
