<?php

namespace App\Filament\Resources\BudgetItems\Pages;

use App\Filament\Resources\BudgetItems\BudgetItemResource;
use App\Filament\Resources\Pages\FoundationCreateRecord;

class CreateRecord extends FoundationCreateRecord
{
    protected static string $resource = BudgetItemResource::class;
}
