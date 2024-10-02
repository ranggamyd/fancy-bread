<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Sale;
use App\Enums\Status;
use App\Models\Driver;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use App\Filament\Exports\SaleExporter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\CustomerResource;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Grouping\Group as GroupFilter;
use Filament\Tables\Actions\Action as CustomAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleResource\Pages\EditSale;
use App\Filament\Resources\SaleResource\Pages\ViewSale;
use App\Filament\Resources\SaleResource\Pages\ListSales;
use App\Filament\Resources\SaleResource\Pages\CreateSale;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use App\Filament\Resources\CustomerResource\RelationManagers\SalesRelationManager;
use App\Filament\Resources\DriverResource\RelationManagers\SalesRelationManager as SalesRelationManagerOnDriverResource;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $recordTitleAttribute = 'invoice';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form, Customer $customer = null, Driver $driver = null): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Sale Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('code')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default('FC/S/' . strtotime(now()))
                            ->unique(ignoreRecord: true),

                        TextInput::make('invoice')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(function () {
                                $currentYear = Carbon::now()->year;
                                $latestInvoice = Sale::whereYear('created_at', $currentYear)->latest()->first();
                                $newNumber = $latestInvoice ? (int) substr($latestInvoice->invoice, -4) + 1 : 1;

                                return 'FC/SC/' . $currentYear . '/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                            })
                            ->unique(ignoreRecord: true),

                        TextInput::make('goods_receipt_number')
                            ->label('No. LPB')
                            ->hidden(fn(?Sale $record) => $record === null)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set) => $set('status', 'delivered')),

                        DateTimePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->closeOnDateSelection()
                            ->columnStart(['lg' => 4]),
                    ])->columns(['lg' => 4]),

                    Select::make('customer_id')
                        ->required()
                        ->relationship('customer', modifyQueryUsing: fn(Builder $query) => $query->orderBy('code')->orderBy('name'))
                        ->getOptionLabelFromRecordUsing(fn(Model $record) => "({$record->code}) {$record->name} - {$record->short_address}")
                        ->searchable(['code', 'name', 'short_address'])
                        ->default($customer?->id)
                        ->disabledOn(SalesRelationManager::class)
                        ->preload()
                        ->searchable()
                        ->manageOptionForm(fn(Form $form) => CustomerResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl')),

                    ToggleButtons::make('status')
                        ->inline()
                        ->options(Status::class)
                        ->default('new')
                        ->hidden(fn(?Sale $record) => $record === null)
                        ->required(),

                    Textarea::make('notes')->rows(1),
                ]),

                Section::make('Sale Items')->schema([static::getRepeaterSaleItems()]),

                Section::make()->schema([
                    TextInput::make('total_items')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(fn(Get $get) => array_sum(array_column($get('saleItems'), 'qty')))
                        ->minValue(1)
                        ->inlineLabel(),

                    TextInput::make('subtotal')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(fn(Get $get) => array_sum(array_column($get('saleItems'), 'total')))
                        ->prefix('Rp.')
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),

                Section::make()->schema([
                    Select::make('driver_id')
                        ->required()
                        ->relationship('driver', 'name')
                        ->default($driver?->id)
                        ->disabledOn(SalesRelationManagerOnDriverResource::class)
                        ->preload()
                        ->searchable()
                        ->manageOptionForm(fn(Form $form) => DriverResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl'))
                        ->inlineLabel(),

                    TextInput::make('shipping_price')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get))
                        ->inlineLabel(),

                    TextInput::make('total_discount')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp.')
                        ->inlineLabel(),

                    TextInput::make('grandtotal')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(fn(Get $get) => array_sum(array_column($get('saleItems'), 'total')))
                        ->prefix('Rp.')
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),
            ])->columns(3)->columnSpan(['lg' => fn(?Sale $record): ?string => $record ? 3 : 4]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Sale $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Sale $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => fn(?Sale $record): ?string => $record ? 1 : 0])->hidden(fn(?Sale $record) => $record === null),
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

                TextColumn::make('status')
                    ->alignCenter()
                    ->badge()
                    ->action(static::getDeliveredForm()),

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

                TextColumn::make('goods_receipt_number')
                    ->label('No. LPB')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer.short_address')
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

                TextColumn::make('saleItems.product.name')
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

                TextColumn::make('driver.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                static::getDeliveredForm(),

                CustomAction::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn(Sale $record): string => route('sales.invoice.print', $record))
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
                Filter::make('date')
                    ->form([
                        DatePicker::make('sold_from'),
                        DatePicker::make('sold_until')->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sold_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['sold_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('status')
                    ->options(Status::class)
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('driver')
                    ->relationship('driver', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                    
                TrashedFilter::make(),
            ])
            ->groups([
                GroupFilter::make('date')->label('Sold at')->date()->collapsible(),
                GroupFilter::make('customer.name')->collapsible(),
                GroupFilter::make('status')->collapsible(),
                GroupFilter::make('driver.name')->collapsible(),
            ])
            ->checkIfRecordIsSelectableUsing(fn(Model $record): bool => $record->goods_receipt_number !== null)
            ->selectCurrentPageOnly()
            ->groupedBulkActions([
                BulkAction::make('Return sales')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        foreach ($records as $item) {
                            if ($item->status === Status::Returned) {
                                return Notification::make()
                                    ->danger()
                                    ->title('Failed')
                                    ->body('Sales have been returned.')
                                    ->send();
                            }

                            if ($item->customer_id !== $records[0]['customer_id']) {
                                return Notification::make()
                                    ->danger()
                                    ->title('Failed')
                                    ->body('Cannot return with multiple customers.')
                                    ->send();
                            }
                        };

                        return redirect(SaleReturnResource::getUrl('create', ['sales' => implode(',', $records->pluck('id')->toArray())]));
                    }),

                BulkAction::make('Create new receipt')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->action(function (Collection $records) {
                        foreach ($records as $item) {
                            if ($item->saleReceiptInvoice) {
                                return Notification::make()
                                    ->danger()
                                    ->title('Failed')
                                    ->body('Sales Receipt have been created.')
                                    ->send();
                            }
                        };

                        return redirect(SaleReceiptResource::getUrl('create', ['sales' => implode(',', $records->pluck('id')->toArray())]));
                    }),

                ExportBulkAction::make()->exporter(SaleExporter::class)
            ]);
    }

    public static function getWidgets(): array
    {
        return [SaleStats::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'view' => ViewSale::route('/{record}'),
            'edit' => EditSale::route('/{record}/edit'),
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
        return [date('d M Y', strtotime($record->date)) . ' : ' . $record->customer?->name . ' - ' . $record->customer?->short_address];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::$model::where('status', Status::New)->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New sales';
    }

    static function calcTotal(Set $set, Get $get)
    {
        $product = Product::find($get('product_id'));

        $set('price', $product?->post_tax_price);
        $set('total', ($get('price') ?: 0) * ($get('qty') ?: 0) * (1 - ($get('discount') ?: 0) / 100));
    }

    static function calcGrandTotal(Set $set, Get $get)
    {
        $totalItems = collect($get('saleItems'))->reduce(fn($totalItems, $item) => $totalItems + ($item['qty'] ?: 0), 0);
        $set('total_items', $totalItems);

        $totalDiscount = collect($get('saleItems'))->reduce(fn($totalDiscount, $item) => $totalDiscount + ($item['price'] ?: 0) * ($item['qty'] ?: 0) * ($item['discount'] ?: 0) / 100, 0);
        $set('total_discount', $totalDiscount);

        $subTotal = collect($get('saleItems'))->reduce(fn($subTotal, $item) => $subTotal + ($item['price'] ?: 0) * ($item['qty'] ?: 0) * (1 - ($item['discount'] ?: 0) / 100), 0);
        $set('subtotal', $subTotal);

        $set('grandtotal', $subTotal + ($get('shipping_price') ?: 0));
    }

    static function getRepeaterSaleItems()
    {
        return Repeater::make('saleItems')
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
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get))
                    ->createOptionForm(fn(Form $form) => ProductResource::form($form))
                    ->createOptionAction(fn(Action $action) => $action->modalWidth('6xl'))
                    ->columnSpan(2),

                TextInput::make('price')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->prefix('Rp.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(function (?Sale $sale, Get $get, $state) {
                        $product = Product::find($get('product_id'));
                        return $sale === null ? $product->stock : $product->stock + $state;
                    })
                    ->validationAttribute('quantity')
                    ->validationMessages(['max' => 'The :attribute field must not be greater than stock of product.'])
                    ->live()
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
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
            ->deleteAction(fn(Action $action) => $action->after(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get)))
            ->itemLabel(function (array $state): ?string {
                $product = Product::find($state['product_id']);
                if (!$product) return null;

                $stock = $product->stock;

                return "($product->code) $product->name - $stock products available.";
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
                $product->stock = $product->stock - $data['qty'];
                $product->save();

                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function ($data, $record) {
                $product = Product::find($data['product_id']);
                $product->stock = $product->stock + $record->qty - $data['qty'];
                $product->save();

                return $data;
                // problem: kalo hapus item repeater pas edit stoknya gimana?
            });
    }

    static function getDeliveredForm()
    {
        return CustomAction::make('setLPB')
            ->label('Delivered?')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->hidden(fn(Sale $record) => $record->status !== Status::New)
            ->modalHeading('Goods Receipt')
            ->form([
                TextInput::make('goods_receipt_number')
                    ->label('No. LPB')
                    ->required()
                    ->default('LPB/' . strtotime(now()))
                    ->unique(ignoreRecord: true)
                    ->helperText('fill in this field would update the sale status to delivered.'),
            ])
            ->action(function ($record, $data) {
                $record->goods_receipt_number = $data['goods_receipt_number'];
                $record->status = Status::Delivered;
                $record->save();
            });
    }
}
