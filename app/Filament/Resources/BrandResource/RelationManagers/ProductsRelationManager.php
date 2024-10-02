<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use App\Filament\Resources\ProductResource;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return ProductResource::form($form, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return ProductResource::table($table)->headerActions([CreateAction::make()]);
    }
}
