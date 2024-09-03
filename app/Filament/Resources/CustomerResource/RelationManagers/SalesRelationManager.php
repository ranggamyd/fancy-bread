<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\SaleResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';

    public function form(Form $form): Form
    {
        return SaleResource::form($form, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return SaleResource::table($table)->headerActions([Tables\Actions\CreateAction::make()]);
    }
}
