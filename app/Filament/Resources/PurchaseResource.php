<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PurchaseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseResource\RelationManagers;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationGroup = 'Transaction';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Purchase Information')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('code')
                                            ->required()
                                            ->readOnly()
                                            ->default(function () {
                                                $lastCode = Purchase::latest('id')->value('code');
                                                if (!$lastCode) return 'TPC0001';
                                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                                            })
                                            ->unique(ignoreRecord: true),

                                        Forms\Components\TextInput::make('invoice')
                                            ->required()
                                            ->readOnly()
                                            ->default(function () {
                                                $lastCode = Purchase::latest('id')->value('code');
                                                if (!$lastCode) return 'INV0001';
                                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                                            })
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Forms\Components\Select::make('vendor_id')
                                    ->required()
                                    ->relationship('vendor', 'name')
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\MarkdownEditor::make('notes'),
                            ]),

                        Forms\Components\Section::make('Purchase Items')
                            ->schema([
                                Forms\Components\Repeater::make('purchaseItems')
                                    ->required()
                                    ->hiddenLabel()
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Product')
                                            ->required()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->options(Product::query()->pluck('name', 'id'))
                                            ->live(true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                $price = Product::find($state)?->post_tax_price ?? 0;
                                                $set('price', (float) $price);
                                                $set('subtotal', (float) $price);
                                            })
                                            ->preload()
                                            ->searchable(),

                                        Forms\Components\TextInput::make('price')
                                            ->readOnly()
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp.')
                                            ->default(0),

                                        Forms\Components\TextInput::make('qty')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            // ->afterStateUpdated(fn($state, Forms\Set $set) => $set('subtotal', (int) $state * (float) $set('price'))
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                $set('subtotal', $state * (float) $set('price'));
                                            }),

                                        Forms\Components\TextInput::make('subtotal')
                                            ->readOnly()
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp.')
                                            ->default(0),
                                    ])
                                    ->columns(4)
                            ]),

                        Forms\Components\Section::make('')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_price')
                                    ->readOnly()
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->prefix('Rp.')
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->default(0)
                                    ->inlineLabel(),

                                Forms\Components\TextInput::make('discount')
                                    ->readOnly()
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->prefix('Rp.')
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->default(0)
                                    ->inlineLabel(),

                                Forms\Components\TextInput::make('total')
                                    ->readOnly()
                                    ->required()
                                    ->numeric()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->prefix('Rp.')
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->default(0)
                                    ->inlineLabel(),
                            ])
                            ->columnStart(2),
                    ])
                    ->columns(3),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_items')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shipping_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('discount')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
