<?php

namespace App\Filament\Resources\BudgetItems\Pages;

use App\Filament\Resources\BudgetItems\BudgetItemResource;
use App\Filament\Resources\Pages\FoundationListRecords;

class ListRecords extends FoundationListRecords
{
    protected static string $resource = BudgetItemResource::class;
}
