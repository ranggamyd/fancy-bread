<?php

namespace App\Filament\Resources;

use App\Models\Sale;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\SaleReturn;
use Filament\Tables\Table;
use App\Models\SaleReceipt;
use App\Enums\PaymentStatus;
use Filament\Resources\Resource;
use App\Models\SaleReturnInvoice;
use App\Models\SaleReceiptPayment;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
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
use Filament\Notifications\Actions\Action;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Actions\BulkActionGroup;
use App\Filament\Exports\SaleReceiptExporter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Grouping\Group as GroupFilter;
use Filament\Tables\Actions\Action as CustomAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleReceiptResource\Pages\EditSaleReceipt;
use App\Filament\Resources\SaleReceiptResource\Pages\ViewSaleReceipt;
use App\Filament\Resources\SaleReceiptResource\Pages\ListSaleReceipts;
use App\Filament\Resources\SaleReceiptResource\Pages\CreateSaleReceipt;
use App\Filament\Resources\SaleReceiptResource\Widgets\SaleReceiptStats;
use App\Filament\Resources\SaleReceiptResource\RelationManagers\SaleReceiptPaymentsRelationManager;

class SaleReceiptResource extends Resource
{
    protected static ?string $model = SaleReceipt::class;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Receipts';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $saleIds = explode(',', request('sales'));

        $saleReceiptInvoices = Sale::whereIn('id', $saleIds)->get()->map(function ($sale) {
            return [
                'sale_id' => $sale->id,
                'goods_receipt_number' => $sale->goods_receipt_number,
                'customer_id' => $sale->customer_id,
                'total_items' => $sale->total_items,
                'grandtotal' => $sale->grandtotal,
                'date' => $sale->date,
            ];
        })->values()->toArray();

        $saleReturnInvoices = SaleReturnInvoice::whereIn('sale_id', $saleIds)->with('saleReturn')->get();
        $saleReceiptReturns = $saleReturnInvoices->unique(fn($item) => $item->saleReturn->id)
            ->map(fn($item) => [
                'sale_return_id' => $item->saleReturn->id,
                'total_items' => $item->saleReturn->total_items,
                'grandtotal' => $item->saleReturn->grandtotal,
                'date' => $item->saleReturn->date,
            ])->values()->toArray();

        return $form->schema([
            Group::make()->schema([
                Section::make('Sale Receipt Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default('FC/S/RCPT/' . strtotime(now()))
                            ->unique(ignoreRecord: true),

                        TextInput::make('branch')
                            ->required()
                            ->default('PLUMBON'),

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

                Section::make('Sale Invoices')->schema([
                    static::getRepeaterSaleReceiptInvoices($saleReceiptInvoices)->columnSpanFull(),

                    Section::make()->schema([
                        TextInput::make('invoice_items')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->default(array_reduce($saleReceiptInvoices, fn($i, $item) => $i + $item['total_items'], 0))
                            ->inlineLabel(),

                        TextInput::make('total_invoice')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(array_reduce($saleReceiptInvoices, fn($i, $item) => $i + $item['grandtotal'], 0))
                            ->inlineLabel(),
                    ]),
                ])->collapsed(),

                Section::make('Sale Returns')->schema([
                    static::getRepeaterSaleReceiptReturns($saleReceiptReturns)->columnSpanFull(),

                    Section::make()->schema([
                        TextInput::make('return_items')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->default(array_reduce($saleReceiptReturns, fn($i, $item) => $i + $item['total_items'], 0))
                            ->inlineLabel(),

                        TextInput::make('total_return')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->default(array_reduce($saleReceiptReturns, fn($i, $item) => $i + $item['grandtotal'], 0))
                            ->inlineLabel(),
                    ]),
                ])->collapsed(),

                Section::make()->schema([
                    TextInput::make('total_invoice')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(array_reduce($saleReceiptInvoices, fn($i, $item) => $i + $item['grandtotal'], 0))
                        ->inlineLabel(),

                    TextInput::make('total_return')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(array_reduce($saleReceiptReturns, fn($i, $item) => $i + $item['grandtotal'], 0))
                        ->inlineLabel(),

                    TextInput::make('subtotal')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(fn(Get $get) => ($get('total_invoice') ?: 0) - ($get('total_return') ?: 0))
                        ->prefix('Rp.')
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),

                Section::make()->schema([
                    TextInput::make('fee')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp.')
                        ->default(10000)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, Get $get, $state) => $set('grandtotal', ($get('subtotal') ?: 0) - $state))
                        ->inlineLabel(),

                    TextInput::make('grandtotal')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(fn(Get $get) => ($get('total_invoice') ?: 0) - ($get('total_return') ?: 0) - ($get('fee') ?: 0))
                        ->prefix('Rp.')
                        ->inlineLabel(),
                ])->columnStart(['lg' => 2]),
            ])->columns(3)->columnSpan(['lg' => fn(?SaleReceipt $record): ?string => $record ? 3 : 4]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(SaleReceipt $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(SaleReceipt $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => fn(?SaleReceipt $record): ?string => $record ? 1 : 0])->hidden(fn(?SaleReceipt $record) => $record === null),
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

                TextColumn::make('payment_status')
                    ->alignCenter()
                    ->badge()
                    ->action(static::getPaymentForm()),

                TextColumn::make('code')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('saleReceiptInvoices.sale.customer.name')
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

                TextColumn::make('invoice_items')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_invoice')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('return_items')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_return')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subtotal')
                    ->money('IDR')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('fee')
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

                TextColumn::make('paid')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->saleReceiptPayments->sum('total'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('remaining_balance')
                    ->label('Remaining')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->grandtotal - $record->saleReceiptPayments->sum('total'))
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                static::getPaymentForm(),

                ActionGroup::make([
                    CustomAction::make('print_receipt')
                        ->label('Kwitansi')
                        ->icon('heroicon-o-printer')
                        ->url(function (SaleReceipt $record): string {
                            return route('saleReceipts.receipt.print', $record);
                        })
                        ->openUrlInNewTab()
                        ->color('primary'),

                    CustomAction::make('print_invoices_receipt')
                        ->label('Tanda Penyerahan TTF')
                        ->icon('heroicon-o-printer')
                        ->url(function (SaleReceipt $record): string {
                            return route('saleReceipts.invoices.print', $record);
                        })
                        ->openUrlInNewTab(),
                ])->icon('heroicon-o-printer')->label('Print'),

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
                        DatePicker::make('receipt_from'),
                        DatePicker::make('receipt_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['receipt_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['receipt_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                SelectFilter::make('customer')
                    ->relationship('saleReceiptInvoices.sale.customer', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->groups([GroupFilter::make('date')->date()->collapsible()])
            ->groupedBulkActions([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(SaleReceiptExporter::class)]);
    }

    public static function getRelations(): array
    {
        return [SaleReceiptPaymentsRelationManager::class];
    }

    public static function getWidgets(): array
    {
        return [SaleReceiptStats::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleReceipts::route('/'),
            'create' => CreateSaleReceipt::route('/create'),
            'view' => ViewSaleReceipt::route('/{record}'),
            'edit' => EditSaleReceipt::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'saleReceiptInvoices.sale.code', 'saleReceiptInvoices.sale.invoice', 'saleReceiptReturns.code'];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['saleReceiptInvoices', 'saleReceiptReturns']);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [date('d M Y', strtotime($record->date))];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::$model::where('payment_status', '!=', 'paid')->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Unpaid/uncomplete payments';
    }

    static function getRepeaterSaleReceiptInvoices($saleReceiptInvoices)
    {
        return Repeater::make('saleReceiptInvoices')
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
                    ->label('Qty')
                    ->required()
                    ->disabled(),

                TextInput::make('grandtotal')
                    ->label('Total')
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
            ->default($saleReceiptInvoices)
            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                $sale = Sale::find($data['sale_id']);

                $data['goods_receipt_number'] = $sale->goods_receipt_number;
                $data['customer_id'] = $sale->customer_id;
                $data['total_items'] = $sale->total_items;
                $data['grandtotal'] = $sale->grandtotal;
                $data['date'] = $sale->date;

                return $data;
            })
        ;
    }

    static function getRepeaterSaleReceiptReturns($saleReceiptReturns)
    {
        return Repeater::make('saleReceiptReturns')
            ->relationship()
            ->hiddenLabel()->schema([
                Select::make('sale_return_id')
                    ->label('Return')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->relationship('saleReturn', 'code'),

                TextInput::make('total_items')
                    ->label('Qty')
                    ->required()
                    ->disabled(),

                TextInput::make('grandtotal')
                    ->label('Total')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp.'),

                DateTimePicker::make('date')
                    ->required()
                    ->disabled()
                    ->suffixIcon('heroicon-o-calendar'),
            ])
            ->columns(4)
            ->addable(false)
            ->reorderable(false)
            ->deletable(false)
            ->default($saleReceiptReturns)
            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                $saleReturn = SaleReturn::find($data['sale_return_id']);

                $data['total_items'] = $saleReturn->total_items;
                $data['grandtotal'] = $saleReturn->grandtotal;
                $data['date'] = $saleReturn->date;

                return $data;
            })
        ;
    }

    static function getPaymentForm()
    {
        return CustomAction::make('pay')
            ->hidden(fn(SaleReceipt $record) => $record->payment_status === PaymentStatus::Paid)
            ->icon('heroicon-o-banknotes')
            ->color('info')
            ->modalHeading('Add new payment')
            ->form([
                Grid::make()->schema([
                    ToggleButtons::make('method')
                        ->required()
                        ->options([
                            'bank_transfer' => 'Bank transfer',
                            'cash_on_delivery' => 'Cash on delivery',
                            'credit_card' => 'Credit card',
                        ])
                        ->inline()
                        ->live(),

                    DateTimePicker::make('date')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->suffixIcon('heroicon-o-calendar')
                        ->closeOnDateSelection(),
                ]),

                Grid::make()->schema([
                    ToggleButtons::make('provider')
                        ->required()
                        ->inline()
                        ->grouped()
                        ->options([
                            'bri' => 'BRI',
                            'bca' => 'BCA',
                            'mandiri' => 'Mandiri',
                            'uob' => 'UOB',
                        ]),

                    TextInput::make('reference')->required(),
                ])->hidden((fn(Get $get) => $get('method') === 'cash_on_delivery')),

                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp.')
                    ->default(fn($record) => $record->grandtotal - $record->saleReceiptPayments->sum('total'))
                    ->minValue(1)
                    ->columnSpan('full')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set) => $set('total', ($get('amount') ?: 0) + ($get('fee') ?: 0))),

                Grid::make()->schema([
                    TextInput::make('fee')
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Get $get, Set $set) => $set('total', ($get('amount') ?: 0) + ($get('fee') ?: 0))),

                    TextInput::make('total')
                        ->required()
                        ->readOnly()
                        ->numeric()
                        ->prefix('Rp.')
                        ->default(fn($record) => $record->grandtotal - $record->saleReceiptPayments->sum('total')),
                ])
            ])
            ->action(function ($record, $data) {
                $data['sale_receipt_id'] = $record->id;
                $saleReceiptPayment = SaleReceiptPayment::create($data);
                $saleReceiptPayment->save();

                if ($record->saleReceiptPayments->sum('total') >= $record->grandtotal) {
                    $record->payment_status = PaymentStatus::Paid;
                } elseif ($record->saleReceiptPayments->sum('total') == 0) {
                    $record->payment_status = PaymentStatus::Unpaid;
                } else {
                    $record->payment_status = PaymentStatus::Uncomplete;
                }

                $record->save();

                $total = number_format($saleReceiptPayment->total, 2, '.', ',');

                Notification::make()
                    ->icon('heroicon-o-banknotes')
                    ->title("#{$record->code}")
                    ->body("Rp. {$total} placed for this receipt.")
                    ->actions([Action::make('Detail')->url(SaleReceiptResource::getUrl('view', ['record' => $saleReceiptPayment->saleReceipt]))])
                    ->sendToDatabase(Auth::user());
            });
    }
}
