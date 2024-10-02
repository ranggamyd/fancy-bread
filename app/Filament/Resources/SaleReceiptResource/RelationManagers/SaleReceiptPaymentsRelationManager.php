<?php

namespace App\Filament\Resources\SaleReceiptResource\RelationManagers;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Actions\Action;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use App\Filament\Resources\SaleReceiptResource;
use Filament\Resources\RelationManagers\RelationManager;

class SaleReceiptPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'saleReceiptPayments';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->default(fn() => $this->getOwnerRecord()->grandtotal - $this->getOwnerRecord()->saleReceiptPayments->sum('total'))
                    ->minValue(1)
                    ->columnSpan('full')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set) => $set('total', ($get('amount') ?: 0) + ($get('fee') ?: 0))),

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
                    ->default(fn() => $this->getOwnerRecord()->grandtotal - $this->getOwnerRecord()->saleReceiptPayments->sum('total')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sale Payments')
            ->columns([
                TextColumn::make('date')
                    ->alignCenter()
                    ->date()
                    // ->since()
                    // ->dateTimeTooltip()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                ColumnGroup::make('Context')
                    ->columns([
                        TextColumn::make('method')
                            ->formatStateUsing(fn($state) => Str::headline($state))
                            ->sortable(),

                        TextColumn::make('reference')
                            ->searchable(),

                        TextColumn::make('provider')
                            ->formatStateUsing(fn($state) => Str::upper($state))
                            ->sortable(),
                    ]),

                ColumnGroup::make('Details')
                    ->columns([

                        TextColumn::make('amount')
                            ->sortable()
                            ->money('IDR'),

                        TextColumn::make('fee')
                            ->sortable()
                            ->money('IDR'),

                        TextColumn::make('total')
                            ->sortable()
                            ->money('IDR'),
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New payment')
                    ->after(function ($record) {
                        static::setStatus($record);

                        $saleReceiptPayment = $record;

                        $total = number_format($saleReceiptPayment->total, 2, '.', ',');

                        Notification::make()
                            ->icon('heroicon-o-banknotes')
                            ->title("#{$saleReceiptPayment->saleReceipt->code}")
                            ->body("Rp. {$total} placed for this receipt.")
                            ->actions([Action::make('Detail')->url(SaleReceiptResource::getUrl('view', ['record' => $saleReceiptPayment->saleReceipt]))])
                            ->sendToDatabase(Auth::user());
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->color('info')->after(fn($record) => static::setStatus($record)),
                    DeleteAction::make()->after(fn($record) => static::setStatus($record)),
                    ForceDeleteAction::make()->after(fn($record) => static::setStatus($record)),
                    RestoreAction::make()->after(fn($record) => static::setStatus($record)),
                ])
            ])
            ->filters([TrashedFilter::make()])
            ->groupedBulkActions([DeleteBulkAction::make()]);
    }

    protected function setStatus($record)
    {
        $saleReceipt = $record->saleReceipt;

        if ($saleReceipt->saleReceiptPayments->sum('total') >= $saleReceipt->grandtotal) {
            $saleReceipt->payment_status = PaymentStatus::Paid;
        } elseif ($saleReceipt->saleReceiptPayments->sum('total') == 0) {
            $saleReceipt->payment_status = PaymentStatus::Unpaid;
        } else {
            $saleReceipt->payment_status = PaymentStatus::Uncomplete;
        }

        $saleReceipt->save();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
