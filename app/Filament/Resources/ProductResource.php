<?php

namespace App\Filament\Resources;

use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\BrandResource;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\CategoryResource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form, Brand $brand = null, Category $category = null): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Product Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default(function () {
                                $lastCode = Product::latest('id')->value('code');
                                if (!$lastCode) return 'P0001';
                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                            })->unique(ignoreRecord: true),
                    ]),

                    MarkdownEditor::make('description'),
                ]),

                Section::make('Product Image')->schema([
                    FileUpload::make('image')
                        ->hiddenLabel()
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                        ->directory('images/brands')
                ])->collapsible(),

                Section::make('Pricing')->schema([
                    Grid::make()->schema([
                        TextInput::make('post_tax_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, $state) => $set('pre_tax_price', $state / 1.11)),

                        TextInput::make('pre_tax_price')
                            ->readOnly()
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(0),
                    ]),

                    Grid::make()->schema([
                        TextInput::make('cost')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(0),

                        TextInput::make('margin')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(0),
                    ]),
                ]),

                Section::make('Inventory')->schema([
                    Grid::make()->schema([
                        TextInput::make('sku')
                            ->label('SKU (Stock Keeping Unit)')
                            ->unique(ignoreRecord: true),

                        TextInput::make('barcode')
                            ->label('Barcode (ISBN, UPC, GTIN, etc.)')
                            ->unique(ignoreRecord: true),
                    ]),

                    Grid::make()->schema([
                        TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(1),

                        TextInput::make('security_stock')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->helperText('The safety stock is the limit stock for your products which alerts you if the product stock will soon be out of stock.'),
                    ]),

                    Grid::make()->schema([
                        Select::make('unit_type')
                            ->required()
                            ->options(['Pcs' => 'Pcs', 'Pack/Box' => 'Pack/Box', 'Kg' => 'Kg'])
                            ->preload()
                            ->searchable(),

                        TextInput::make('total_items')
                            ->required()
                            ->numeric()
                            ->default(1),
                    ]),
                ]),
            ])->columnSpan(['lg' => 2]),

            Group::make()->schema([
                Section::make('Associations')->schema([
                    Select::make('brand_id')
                        ->required()
                        ->relationship('brand', 'name')
                        ->default($brand?->id)
                        ->disabledOn(ProductsRelationManager::class)
                        ->preload()
                        ->searchable()
                        ->manageOptionForm(fn(Form $form) => BrandResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl')),

                    Select::make('category_id')
                        ->relationship('categories', 'name')
                        ->default([$category?->id])
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->manageOptionForm(fn(Form $form) => CategoryResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl')),
                ]),

                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Product $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Product $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible()->hidden(fn(?Product $record) => $record === null),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->alignCenter()
                    ->defaultImageUrl(url('/images/default.jpg'))
                    ->size(40)
                    ->toggleable(),

                TextColumn::make('code')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('categories.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('pre_tax_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('post_tax_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('cost')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('margin')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('barcode')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('stock')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('security_stock')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('unit_type')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_items')
                    ->alignCenter()
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
                        NumberConstraint::make('pre_tax_price')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('post_tax_price')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('cost')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('margin')->icon('heroicon-m-currency-dollar'),
                        TextConstraint::make('sku')->label('SKU (Stock Keeping Unit)'),
                        TextConstraint::make('barcode')->label('Barcode (ISBN, UPC, GTIN, etc.)'),
                        NumberConstraint::make('stock'),
                        NumberConstraint::make('security_stock'),
                        SelectConstraint::make('unit_type')->options(['Pcs', 'Pack/Box', 'Kg']),
                        NumberConstraint::make('total_items'),
                        DateConstraint::make('created_at'),
                        DateConstraint::make('updated_at'),
                    ])->constraintPickerColumns(2),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultSort('code')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(function ($data, $record) {
                        if ($record->purchaseItems()->exists() || $record->saleItems()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Product is in use')
                                ->body('Product is exist on transactions.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title('Deleted')
                            ->send();

                        $record->delete();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::whereColumn('stock', '<=', 'security_stock')->count();
    }
}
