<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use App\Filament\Clusters\Purchases;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\PurchaseExporter;
use App\Filament\Resources\VendorResource;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ProductResource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Grouping\Group as GroupFilter;
use Filament\Tables\Actions\Action as CustomAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseResource\Pages\EditPurchase;
use App\Filament\Resources\PurchaseResource\Pages\ViewPurchase;
use App\Filament\Resources\PurchaseResource\Pages\ListPurchases;
use App\Filament\Resources\PurchaseResource\Pages\CreatePurchase;
use App\Filament\Resources\PurchaseResource\Widgets\PurchaseStats;
use App\Filament\Resources\VendorResource\RelationManagers\PurchasesRelationManager;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $recordTitleAttribute = 'invoice';

    protected static ?string $navigationGroup = 'Purchases';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form, Vendor $vendor = null): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Purchase Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('code')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default('TP-C-' . strtotime(now()))
                            ->unique(ignoreRecord: true),

                        TextInput::make('invoice')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(function () {
                                $currentYear = Carbon::now()->year;
                                $latestInvoice = Purchase::whereYear('created_at', $currentYear)->latest()->first();
                                $newNumber = $latestInvoice ? (int) substr($latestInvoice->invoice, -4) + 1 : 1;

                                return 'FC/PC/' . $currentYear . '/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                            })
                            ->unique(ignoreRecord: true),

                        DateTimePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->closeOnDateSelection()
                            ->columnStart(['lg' => 4]),
                    ])->columns(['lg' => 4]),

                    Select::make('vendor_id')
                        ->required()
                        ->relationship('vendor', modifyQueryUsing: fn(Builder $query) => $query->orderBy('code')->orderBy('name'))
                        ->getOptionLabelFromRecordUsing(fn(Model $record) => "({$record->code}) {$record->name} - {$record->short_address}")
                        ->searchable(['code', 'name', 'short_address'])
                        ->default($vendor?->id)
                        ->disabledOn(PurchasesRelationManager::class)
                        ->preload()
                        ->searchable()
                        ->manageOptionForm(fn(Form $form) => VendorResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl')),

                    Textarea::make('notes')
                        ->rows(1),
                ]),

                Section::make('Purchase Items')->schema([static::getRepeaterPurchaseItems()]),

                Section::make()->schema([
                    TextInput::make('total_items')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->default(fn(Get $get) => array_sum(array_column($get('purchaseItems'), 'qty')))
                        ->minValue(1)
                        ->inlineLabel(),

                    TextInput::make('subtotal')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(fn(Get $get) => array_sum(array_column($get('purchaseItems'), 'total')))
                        ->minValue(1)
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),

                Section::make()->schema([
                    TextInput::make('shipping_price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get))
                        ->inlineLabel(),

                    TextInput::make('total_discount')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->inlineLabel(),

                    TextInput::make('grandtotal')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(fn(Get $get) => array_sum(array_column($get('purchaseItems'), 'total')))
                        ->minValue(1)
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),
            ])->columns(3)->columnSpan(['lg' => fn(?Purchase $record): ?string => $record ? 3 : 4]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Purchase $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Purchase $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => fn(?Purchase $record): ?string => $record ? 1 : 0])->hidden(fn(?Purchase $record) => $record === null),
        ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('date')
                    ->alignCenter()
                    ->date()
                    // ->since()
                    // ->dateTimeTooltip()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('code')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('invoice')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('vendor.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('vendor.short_address')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('purchaseItems.product.name')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('total_items')
                    ->label('Items')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subtotal')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('shipping_price')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('discount')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('grandtotal')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                CustomAction::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn(Purchase $record): string => route('purchases.invoice.print', $record))
                    ->openUrlInNewTab()
                    ->color('primary'),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->color('info'),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
            ])
            ->filters([
                // Filter::make('date')
                //     ->form([
                //         DatePicker::make('purchased_from'),
                //         DatePicker::make('purchased_until'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['purchased_from'] ?? null,
                //                 fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                //             )
                //             ->when(
                //                 $data['purchased_until'] ?? null,
                //                 fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                //             );
                //     }),

                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->groups([
                GroupFilter::make('date')
                    ->label('Purchased at')
                    ->date()
                    ->collapsible(),

                GroupFilter::make('vendor.name')->collapsible(),
            ])
            ->groupedBulkActions([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(PurchaseExporter::class)]);
    }

    public static function getWidgets(): array
    {
        return [PurchaseStats::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'view' => ViewPurchase::route('/{record}'),
            'edit' => EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'invoice'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [date('d M Y', strtotime($record->date)) . ' : ' . $record->vendor?->name . ' - ' . $record->vendor?->short_address];
    }

    static function calcTotal(Set $set, Get $get)
    {
        $set('total', ($get('price') ?: 0) * ($get('qty') ?: 0) * (1 - ($get('discount') ?: 0) / 100));
    }

    static function calcGrandTotal(Set $set, Get $get)
    {
        $totalItems = collect($get('purchaseItems'))->reduce(fn($totalItems, $item) => $totalItems + ($item['qty'] ?: 0), 0);
        $set('total_items', $totalItems);

        $totalDiscount = collect($get('purchaseItems'))->reduce(fn($totalDiscount, $item) => $totalDiscount + ($item['price'] ?: 0) * ($item['qty'] ?: 0) * ($item['discount'] ?: 0) / 100, 0);
        $set('total_discount', $totalDiscount);

        $subTotal = collect($get('purchaseItems'))->reduce(fn($subTotal, $item) => $subTotal + ($item['price'] ?: 0) * ($item['qty'] ?: 0) * (1 - ($item['discount'] ?: 0) / 100), 0);
        $set('subtotal', $subTotal);

        $set('grandtotal', $subTotal + ($get('shipping_price') ?: 0));
    }

    static function getRepeaterPurchaseItems()
    {
        return Repeater::make('purchaseItems')
            ->required()
            ->relationship()
            ->hiddenLabel()->schema([
                Select::make('product_id')
                    ->required()
                    ->relationship('product', 'name')
                    ->preload()
                    ->searchable()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set, $state) => $set('price', Product::find($state)?->post_tax_price))
                    ->createOptionForm(fn(Form $form) => ProductResource::form($form))
                    ->createOptionAction(fn(Action $action) => $action->modalWidth('6xl'))
                    ->columnSpan(2),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp.')
                    ->minValue(1)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->default(0)
                    ->rules(['min:0', 'max:100'])
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('total')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp.'),
            ])
            ->default(Product::all()->map(fn($product) => [
                'product_id' => $product->id,
                'price' => $product->post_tax_price,
                'qty' => 1,
                'discount' => 0,
                'total' => $product->post_tax_price,
            ])->toArray())
            ->collapsed()
            ->reorderable()
            ->columns(6)
            ->live(onBlur: true)
            ->afterStateUpdated(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get))
            ->itemLabel(function (array $state): ?string {
                $product = Product::find($state['product_id']);
                if (!$product) return null;

                return "($product->code) $product->name - $product->stock products left.";
            })
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = Product::find($itemData['product_id']);
                        if (!$product) return null;

                        return ProductResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn(array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
            ])
            ->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                $product = Product::find($data['product_id']);
                $product->stock = $product->stock + $data['qty'];
                $product->save();

                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function ($data, $record) {
                $product = Product::find($data['product_id']);
                $product->stock = $product->stock - $record->qty + $data['qty'];
                $product->save();

                return $data;
                // problem: kalo hapus item repeater pas edit stoknya gimana?
            });
    }
}
