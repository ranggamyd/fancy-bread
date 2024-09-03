<?php

namespace App\Filament\Resources\VendorResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\PurchaseResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
        return PurchaseResource::table($table)->headerActions([Tables\Actions\CreateAction::make()]);
    }
}
