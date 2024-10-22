<?php

namespace App\Filament\Resources;

use App\Models\Driver;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Exports\DriverExporter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\DriverResource\Pages\EditDriver;
use App\Filament\Resources\DriverResource\Pages\ListDrivers;
use App\Filament\Resources\DriverResource\Pages\CreateDriver;
use App\Filament\Resources\DriverResource\RelationManagers\SalesRelationManager;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Miscellaneous';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Driver Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

                    DatePicker::make('birth_date'),

                    Radio::make('gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                        ])
                        ->default('Male')
                        ->inline(),

                    Grid::make()
                        ->schema([
                            TextInput::make('email')
                                ->label('Email address')
                                ->email()
                                ->unique(Driver::class, 'email', ignoreRecord: true),

                            TextInput::make('phone')
                                ->label('Phone number')
                                ->tel()
                                ->unique(Driver::class, 'phone', ignoreRecord: true),
                        ]),

                    Textarea::make('address')->columnSpanFull(),
                ]),
            ])->columnSpan(['lg' => fn(?Driver $record) => $record === null ? 3 : 2]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Driver $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Driver $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => 1])->hidden(fn(?Driver $record) => $record === null),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Split::make([
                Stack::make([
                    TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('medium')
                        ->alignLeft(),

                    TextColumn::make('address')
                        ->limit(40)
                        ->searchable()
                        ->sortable()
                        ->color('gray')
                        ->alignLeft(),
                ])->space(),

                Stack::make([
                    TextColumn::make('birth_date')
                        ->alignCenter()
                        ->date()
                        ->searchable()
                        ->sortable()
                        ->toggleable(),
                ]),

                Stack::make([
                    TextColumn::make('phone')
                        ->icon('heroicon-m-phone')
                        ->label('Phone')
                        ->alignLeft(),

                    TextColumn::make('email')
                        ->icon('heroicon-m-at-symbol')
                        ->label('E-Mail')
                        ->alignLeft(),
                ])->space(2),
            ])->from('md'),
        ])->actions([
            ActionGroup::make([EditAction::make()->color('info'), DeleteAction::make()])
        ])->groupedBulkActions([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(DriverExporter::class)]);
    }

    public static function getRelations(): array
    {
        return [SalesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrivers::route('/'),
            'create' => CreateDriver::route('/create'),
            'edit' => EditDriver::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'address'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [Str::limit($record->address, 30)];
    }
}
