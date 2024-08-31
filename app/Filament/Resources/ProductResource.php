<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Tables\Filters\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Product Information')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->dehydrateStateUsing(fn(string $state): string => ucwords($state)),

                                        Forms\Components\TextInput::make('code')
                                            ->required()
                                            ->readOnly()
                                            ->default(function () {
                                                $lastCode = Product::latest('id')->value('code');
                                                if (!$lastCode) return 'P0001';
                                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                                            })
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Forms\Components\MarkdownEditor::make('description'),
                            ]),

                        Forms\Components\Section::make('Product Image')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                    ->directory('images/brands')
                            ])
                            ->collapsible(),

                        Forms\Components\Section::make('Pricing')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('post_tax_price')
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->prefix('Rp.')
                                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                            ->default(0)
                                            ->live(true)
                                            ->afterStateUpdated(fn(callable $set) => $set('pre_tax_price', fn(string $state) => preg_replace('/[^0-9.]/', '', $state) / 1.11)),

                                        Forms\Components\TextInput::make('pre_tax_price')
                                            ->readOnly()
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->prefix('Rp.')
                                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                            ->default(0),
                                    ]),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('cost')
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->prefix('Rp.')
                                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                            ->default(0),

                                        Forms\Components\TextInput::make('margin')
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->prefix('Rp.')
                                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                            ->default(0),
                                    ])
                            ]),

                        Forms\Components\Section::make('Inventory')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU (Stock Keeping Unit)')
                                            ->unique(ignoreRecord: true),

                                        Forms\Components\TextInput::make('barcode')
                                            ->label('Barcode (ISBN, UPC, GTIN, etc.)')
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('stock')
                                            ->required()
                                            ->numeric()
                                            ->default(1),

                                        Forms\Components\TextInput::make('security_stock')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->helperText('The safety stock is the limit stock for your products which alerts you if the product stock will soon be out of stock.'),
                                    ]),

                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('unit_type')
                                            ->required()
                                            ->options(['Pcs', 'Pack/Box', 'Kg'])
                                            ->preload()
                                            ->searchable(),

                                        Forms\Components\TextInput::make('total_items')
                                            ->required()
                                            ->numeric()
                                            ->default(1),
                                    ])
                            ]),

                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Associations')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->required()
                                    ->relationship('brand', 'name')
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\Select::make('category_id')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Forms\Components\Section::make('Meta Information')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Created at')
                                    ->content(fn(Product $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Last modified at')
                                    ->content(fn(Product $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->hidden(fn(?Product $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl(url('/images/default.jpg'))
                    ->size(40)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('pre_tax_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('post_tax_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('cost')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('margin')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('barcode')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('stock')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('security_stock')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_items')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('code'),
                        TextConstraint::make('name'),
                        RelationshipConstraint::make('brand')
                            ->selectable(
                                IsRelatedToOperator::make()
                                    ->titleAttribute('name')
                                    ->preload()
                                    ->searchable(),
                            ),
                        RelationshipConstraint::make('categories')
                            ->selectable(
                                IsRelatedToOperator::make()
                                    ->titleAttribute('name')
                                    ->preload()
                                    ->searchable()
                                    ->multiple(),
                            ),
                        TextConstraint::make('description'),
                        NumberConstraint::make('pre_tax_price')
                            ->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('post_tax_price')
                            ->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('cost')
                            ->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('margin')
                            ->icon('heroicon-m-currency-dollar'),
                        TextConstraint::make('sku')
                            ->label('SKU (Stock Keeping Unit)'),
                        TextConstraint::make('barcode')
                            ->label('Barcode (ISBN, UPC, GTIN, etc.)'),
                        NumberConstraint::make('stock'),
                        NumberConstraint::make('security_stock'),
                        SelectConstraint::make('unit_type')
                            ->options(['Pcs', 'Pack/Box', 'Kg']),
                        NumberConstraint::make('total_items'),
                        DateConstraint::make('created_at'),
                        DateConstraint::make('updated_at'),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            // ->deferFilters()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::whereColumn('stock', '<', 'security_stock')->count();
    }
}
