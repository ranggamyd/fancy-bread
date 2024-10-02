<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use App\Filament\Exports\UserExporter;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use App\Filament\Resources\UserResource\Pages\ManageUsers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Miscellaneous';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

            DatePicker::make('birth_date')
                // ->native(false)
                ->suffixIcon('heroicon-o-calendar')
                ->closeOnDateSelection(),

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
                        ->required()
                        ->label('Email address')
                        ->email()
                        ->unique(User::class, 'email', ignoreRecord: true),

                    TextInput::make('phone')
                        ->label('Phone number')
                        ->tel()
                        ->unique(User::class, 'phone', ignoreRecord: true),
                ]),

            Grid::make()
                ->schema([
                    TextInput::make('password')
                        ->password()
                        ->required(fn(?User $record) => $record == null)
                        ->nullable(fn(?User $record) => $record != null)
                        ->rule(Password::default())
                        ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                        ->reactive()
                        ->helperText(fn(?User $record) => $record != null ? 'Leave it empty to keep the current password.' : ''),

                    TextInput::make('password_confirmation')
                        ->password()
                        ->requiredWith('password')
                        ->nullable(fn(?User $record) => $record != null)
                        ->dehydrated(false)
                        ->same('password'),
                ]),

            Textarea::make('address')->columnSpanFull(),
        ]);
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
            ActionGroup::make([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!filled($data['password'])) unset($data['password']);

                        return $data;
                    })->color('info'),

                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
        ])->filters([TrashedFilter::make()
        ])->groupedBulkActions([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(UserExporter::class)]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
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
