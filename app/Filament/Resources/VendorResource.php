<?php

namespace App\Filament\Resources;

use App\Models\Vendor;
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
use App\Filament\Resources\VendorResource\Pages\EditVendor;
use App\Filament\Resources\VendorResource\Pages\ListVendors;
use App\Filament\Resources\VendorResource\Pages\CreateVendor;
use App\Filament\Resources\VendorResource\RelationManagers\PurchasesRelationManager;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Vendor Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default(function () {
                                $lastCode = Vendor::latest('id')->value('code');
                                if (!$lastCode) return 'V0001';
                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                            })->unique(ignoreRecord: true),
                    ]),

                    TextInput::make('short_address'),

                    Textarea::make('full_address'),
                ]),
            ])->columnSpan(['lg' => fn(?Vendor $record) => $record === null ? 3 : 2]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Vendor $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Vendor $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => 1])->hidden(fn(?Vendor $record) => $record === null),
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
                        if ($record->purchases()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Vendor is in use')
                                ->body('Vendor is exist on purchases.')
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
        return [PurchasesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }
}
