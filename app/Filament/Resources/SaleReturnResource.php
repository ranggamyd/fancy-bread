<?php

namespace App\Filament\Resources;

use App\Models\Sale;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\SaleReturn;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ProductResource;
use App\Filament\Exports\SaleReturnExporter;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Grouping\Group as GroupFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleReturnResource\Pages\EditSaleReturn;
use App\Filament\Resources\SaleReturnResource\Pages\ViewSaleReturn;
use App\Filament\Resources\SaleReturnResource\Pages\ListSaleReturns;
use App\Filament\Resources\SaleReturnResource\Pages\CreateSaleReturn;

class SaleReturnResource extends Resource
{
    protected static ?string $model = SaleReturn::class;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Returns';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    public static function form(Form $form, Customer $customer = null): Form
    {
        $saleIds = explode(',', request('sales'));

        $saleReturnInvoices = Sale::whereIn('id', $saleIds)->get()->map(function ($sale) {
            return [
                'sale_id' => $sale->id,
                'goods_receipt_number' => $sale->goods_receipt_number,
                'customer_id' => $sale->customer_id,
                'total_items' => $sale->total_items,
                'grandtotal' => $sale->grandtotal,
                'date' => $sale->date,
            ];
        })->values()->toArray();

        return $form->schema([
            Group::make()->schema([
                Section::make('Sale Return Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default('FC/S/RTN/' . strtotime(now()))
                            ->unique(ignoreRecord: true),

                        DateTimePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->closeOnDateSelection()
                            ->columnStart(['lg' => 4]),
                    ])->columns(['lg' => 4]),

                    Textarea::make('notes')->rows(1),
                ]),

                Section::make('Sale Return Invoices')->schema([static::getRepeaterSaleReturnInvoices($saleReturnInvoices)])->collapsed(),

                Section::make('Sale Return Items')->schema([static::getRepeaterSaleReturnItems()]),

                Section::make()->schema([
                    TextInput::make('total_items')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(0)
                        ->minValue(1)
                        ->inlineLabel(),

                    TextInput::make('subtotal')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->minValue(1)
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),

                Section::make()->schema([
                    TextInput::make('grandtotal')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->minValue(1)
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),
            ])->columns(3)->columnSpan(['lg' => fn(?SaleReturn $record): ?string => $record ? 3 : 4]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(SaleReturn $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(SaleReturn $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => fn(?SaleReturn $record): ?string => $record ? 1 : 0])->hidden(fn(?SaleReturn $record) => $record === null),
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
                    ->toggleable(),

                TextColumn::make('saleReturnInvoices.sale.customer.name')
                    ->formatStateUsing(function ($state) {
                        $customer = Customer::where('name', $state)->first();
                        return $customer?->name . ' - ' . $customer?->short_address;
                    })
                    ->distinctList()
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('saleReturnItems.product.name')
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

                TextColumn::make('grandtotal')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->actions([ActionGroup::make([
                ViewAction::make(),
                EditAction::make()->color('info'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])])
            ->filters([
                Filter::make('date')
                    ->form([DatePicker::make('returned_from'), DatePicker::make('returned_until')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['returned_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['returned_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                SelectFilter::make('customer')
                    ->relationship('saleReturnInvoices.sale.customer', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->groups([GroupFilter::make('date')->label('Returned at')->date()->collapsible()])
            ->groupedBulkActions([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(SaleReturnExporter::class)]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleReturns::route('/'),
            'create' => CreateSaleReturn::route('/create'),
            'view' => ViewSaleReturn::route('/{record}'),
            'edit' => EditSaleReturn::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'saleReturnInvoices.sale.code', 'saleReturnInvoices.sale.invoice'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['saleReturnInvoices']);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [date('d M Y', strtotime($record->date)) . ' : ' . $record->saleReturnInvoices?->pluck('sale.customer.name')->join('') . ' - ' . $record->saleReturnInvoices?->pluck('sale.customer.short_address')->join('')];
    }

    static function getRepeaterSaleReturnInvoices($saleReturnInvoices)
    {
        return Repeater::make('saleReturnInvoices')
            ->required()
            ->relationship()
            ->hiddenLabel()->schema([
                Select::make('sale_id')
                    ->label('Invoice')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->relationship('sale', 'invoice'),

                TextInput::make('goods_receipt_number')
                    ->label('No. LPB')
                    ->required()
                    ->disabled(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->required()
                    ->relationship('sale.customer', modifyQueryUsing: fn(Builder $query) => $query->orderBy('code')->orderBy('name'))
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "({$record->code}) {$record->name} - {$record->short_address}")
                    ->disabled(),

                TextInput::make('total_items')
                    ->required()
                    ->disabled(),

                TextInput::make('grandtotal')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp.'),

                DateTimePicker::make('date')
                    ->required()
                    ->disabled()
                    ->suffixIcon('heroicon-o-calendar'),
            ])
            ->columns(6)
            ->addable(false)
            ->reorderable(false)
            ->deletable(false)
            ->default($saleReturnInvoices)
            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                $sale = Sale::find($data['sale_id']);

                $data['goods_receipt_number'] = $sale->goods_receipt_number;
                $data['customer_id'] = $sale->customer_id;
                $data['total_items'] = $sale->total_items;
                $data['grandtotal'] = $sale->grandtotal;
                $data['date'] = $sale->date;

                return $data;
            });
    }

    static function getRepeaterSaleReturnItems()
    {
        return Repeater::make('saleReturnItems')
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
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('price')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->prefix('Rp.')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                TextInput::make('total')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->prefix('Rp.')
                    ->default(0),
            ])
            ->columns(4)
            ->reorderable()
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
            ]);
    }

    static function calcTotal(Set $set, Get $get)
    {
        $product = Product::find($get('product_id'));

        $set('price', $product?->post_tax_price);
        $set('total', ($get('price') ?: 0) * ($get('qty') ?: 0));
    }

    static function calcGrandTotal(Set $set, Get $get)
    {
        $totalItems = collect($get('saleReturnItems'))->reduce(fn($totalItems, $item) => $totalItems + ($item['qty'] ?: 0), 0);
        $set('total_items', $totalItems);

        $subTotal = collect($get('saleReturnItems'))->reduce(fn($subTotal, $item) => $subTotal + ($item['price'] ?: 0) * ($item['qty'] ?: 0), 0);
        $set('subtotal', $subTotal);

        $set('grandtotal', $subTotal);
    }
}
