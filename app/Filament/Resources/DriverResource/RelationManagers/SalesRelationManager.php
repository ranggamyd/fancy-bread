<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\SaleResource;
use Filament\Tables\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';

    public function form(Form $form): Form
    {
        return SaleResource::form($form, null, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return SaleResource::table($table)->headerActions([CreateAction::make()]);
    }
}
