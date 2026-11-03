<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

abstract class FoundationListRecords extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
