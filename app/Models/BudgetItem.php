<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $guarded = ['id'];

    protected $table = 'budget_items';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
