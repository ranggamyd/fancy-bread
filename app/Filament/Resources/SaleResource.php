<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Sale;
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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VendorResource;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ProductResource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Grouping\Group as GroupFilter;
use Filament\Tables\Actions\Action as CustomAction;
use App\Filament\Resources\SaleResource\Pages\EditSale;
use App\Filament\Resources\SaleResource\Pages\ViewSale;
use App\Filament\Resources\SaleResource\Pages\ListSales;
use App\Filament\Resources\SaleResource\Pages\CreateSale;
use App\Filament\Resources\CustomerResource\RelationManagers\SalesRelationManager;
use Filament\Tables\Actions\ActionGroup;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationGroup = 'Transaction';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form, Customer $customer = null): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Sale Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default('TS-C-' . strtotime(now()))
                            ->unique(ignoreRecord: true),

                        TextInput::make('invoice')
                            ->required()
                            ->readOnly()
                            ->default(function () {
                                $currentYear = Carbon::now()->year;
                                $latestInvoice = Sale::whereYear('created_at', $currentYear)
                                    ->latest()
                                    ->first();
                                if ($latestInvoice) {
                                    $latestNumber = (int) substr($latestInvoice->invoice, -4);
                                    $newNumber = $latestNumber + 1;
                                } else {
                                    $newNumber = 1;
                                }
                                return 'FC/SC/' . $currentYear . '/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                            })
                            ->unique(ignoreRecord: true),

                        DateTimePicker::make('date')
                            ->required()
                            ->timezone('Asia/Jakarta')
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
                        ->manageOptionForm(fn(Form $form) => VendorResource::form($form))
                        ->manageOptionActions(fn(Action $action) => $action->modalWidth('6xl')),

                    MarkdownEditor::make('notes'),
                ]),

                Section::make('Sale Items')->schema([
                    Repeater::make('saleItems')
                        ->required()
                        ->relationship()
                        ->hiddenLabel()->schema([
                            Select::make('product_id')
                                ->required()
                                ->relationship('product', 'name')
                                ->reactive()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                // ->live()
                                // ->afterStateUpdated(fn(Set $set) => $set('qty', 0))
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get))
                                ->preload()
                                ->searchable()
                                ->createOptionForm(fn(Form $form) => ProductResource::form($form))
                                ->createOptionAction(fn(Action $action) => $action->modalWidth('6xl')),

                            TextInput::make('price')
                                ->readOnly()
                                ->required()
                                ->numeric()
                                ->prefix('Rp.')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calcTotal($set, $get)),

                            TextInput::make('qty')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->maxValue(fn(Get $get) => Product::find($get('product_id'))?->stock ?? 0)
                                ->live(onBlur: true)
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
                                ->readOnly()
                                ->required()
                                ->numeric()
                                ->prefix('Rp.')
                                ->default(0),
                        ])
                        ->reorderable()
                        ->columns(5)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get))
                        ->deleteAction(fn(Action $action) => $action->after(fn(Set $set, Get $get) => self::calcGrandTotal($set, $get)))
                        ->itemLabel(function (array $state): ?string {
                            $product = Product::find($state['product_id']);
                            if (!$product) return null;

                            return "($product->code) $product->name - $product->stock product(s) available.";
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
                            // problem: kalo hapus item repeater stoknya gimana?
                        }),
                ]),

                Section::make()->schema([
                    TextInput::make('total_items')
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->inlineLabel(),

                    TextInput::make('subtotal')
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
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
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->inlineLabel(),

                    TextInput::make('grandtotal')
                        ->readOnly()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->rules(['min:0'])
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
                TextColumn::make('code')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('invoice')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer.name')
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

                TextColumn::make('date')
                    ->label('Sold at')
                    ->alignCenter()
                    ->date()
                    // ->since()
                    // ->dateTimeTooltip()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('sold_from'),
                        DatePicker::make('sold_until'),
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

                SelectFilter::make('product')
                    ->relationship('saleItems.product', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->color('info'),
                    CustomAction::make('print_invoice')
                        ->label('Invoice')
                        ->icon('heroicon-o-printer')
                        ->url(fn(Sale $record): string => route('sales.invoice.print', $record))
                        ->openUrlInNewTab()
                        ->color('primary'),
                    CustomAction::make('return')
                        ->label('Return')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->url(fn(Sale $record): string => SaleReturnResource::getUrl('create', ['sale_id' => $record->id]))
                        ->openUrlInNewTab()
                        ->color('danger'),
                ])
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->groups([
                GroupFilter::make('date')
                    ->label('Sold at')
                    ->date()
                    ->collapsible(),
                GroupFilter::make('customer.name')
                    ->collapsible(),
            ])
            ->defaultGroup('date');
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
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'view' => ViewSale::route('/{record}'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
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
}
