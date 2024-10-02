<?php

namespace App\Filament\Resources;

use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Filament\Clusters\Products;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Exports\ProductExporter;
use App\Filament\Resources\BrandResource;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\CategoryResource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $cluster = Products::class;

    protected static ?string $recordTitleAttribute = 'name';

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
                            ->hint('Limit of product\'s stock.'),
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
                TextColumn::make('No')
                    ->rowIndex()
                    ->alignCenter()
                    ->toggleable(),

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
            ->defaultSort('code')
            ->actions([ActionGroup::make([
                EditAction::make()->color('info'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])])
            ->filters([
                TrashedFilter::make()->columnSpanFull(),

                QueryBuilder::make()
                    ->constraints([
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
                        NumberConstraint::make('pre_tax_price')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('post_tax_price')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('cost')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('margin')->icon('heroicon-m-currency-dollar'),
                        NumberConstraint::make('stock'),
                        NumberConstraint::make('security_stock'),
                        SelectConstraint::make('unit_type')->options(['Pcs', 'Pack/Box', 'Kg']),
                        NumberConstraint::make('total_items'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->groupedBulkActions([ExportBulkAction::make()->exporter(ProductExporter::class), DeleteBulkAction::make()]);
    }

    public static function getWidgets(): array
    {
        return [ProductStats::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name', 'brand.name', 'categories.name', 'sku', 'barcode'];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['brand', 'categories']);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            $record->brand?->name . ' - ' . $record->categories?->pluck('name')->join(', '),
            Str::limit($record->description, 30)
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::$model::whereColumn('stock', '<=', 'security_stock')->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low stock products';
    }
}
