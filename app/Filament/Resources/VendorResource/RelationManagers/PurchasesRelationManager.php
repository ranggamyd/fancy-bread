<?php

namespace App\Filament\Resources\VendorResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use App\Filament\Resources\PurchaseResource;
use Filament\Resources\RelationManagers\RelationManager;

class PurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchases';

    public function form(Form $form): Form
    {
        return PurchaseResource::form($form, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return PurchaseResource::table($table)->headerActions([CreateAction::make()]);
    }
}
