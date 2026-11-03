<?php

namespace App\Filament\Resources\BudgetItems;

use App\Filament\Resources\BudgetItems\Pages\CreateRecord;
use App\Filament\Resources\BudgetItems\Pages\EditRecord;
use App\Filament\Resources\BudgetItems\Pages\ListRecords;
use App\Filament\Resources\FoundationResource;
use App\Models\BudgetItem;

class BudgetItemResource extends FoundationResource
{
    protected static ?string $model = BudgetItem::class;

    protected static ?string $slug = 'budget-items';

    public static string $resourceKey = 'budget-items';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
