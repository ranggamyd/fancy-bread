<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\RelationManagers\SalesRelationManager;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Customer Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default(function () {
                                $lastCode = Customer::latest('id')->value('code');
                                if (!$lastCode) return 'C0001';
                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                            })->unique(ignoreRecord: true),
                    ]),

                    TextInput::make('short_address'),

                    Textarea::make('full_address'),
                ]),
            ])->columnSpan(['lg' => fn(?Customer $record) => $record === null ? 3 : 2]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Customer $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Customer $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => 1])->hidden(fn(?Customer $record) => $record === null),
        ])->columns(3);
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

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('short_address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('full_address')
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('code')
            ->filters([
                //
            ])
            ->deferFilters()
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(function ($data, $record) {
                        if ($record->sales()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Customer is in use')
                                ->body('Customer is exist on sales.')
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
        return [SalesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
