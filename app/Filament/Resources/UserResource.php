<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Validation\Rules\Password;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ManageUsers;
use Filament\Forms\Components\Grid;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

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

            MarkdownEditor::make('address')
                ->columnSpanFull(),
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
                        ->toggleable()
                        ->toggledHiddenByDefault(),
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
        ])->filters([
            //
        ])->actions([
            EditAction::make()
                ->mutateFormDataUsing(function ($record, array $data): array {
                    if (!filled($data['password'])) unset($data['password']);

                    return $data;
                }),
            DeleteAction::make(),
        ])->groupedBulkActions([DeleteBulkAction::make()]);
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
            'index' => ManageUsers::route('/'),
        ];
    }
}
