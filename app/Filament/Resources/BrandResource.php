<?php

namespace App\Filament\Resources;

use App\Models\Brand;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Section::make('Brand Information')->schema([
                    Grid::make()->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($component, $state) => $component->state(ucwords(strtolower($state)))),

                        TextInput::make('code')
                            ->required()
                            ->readOnly()
                            ->default(function () {
                                $lastCode = Brand::latest('id')->value('code');
                                if (!$lastCode) return 'B0001';
                                return substr($lastCode, 0, 1) . str_pad((int) substr($lastCode, 1) + 1, 4, '0', STR_PAD_LEFT);
                            })->unique(ignoreRecord: true),
                    ]),

                    MarkdownEditor::make('description'),
                ]),

                Section::make('Brand Image')->schema([
                    FileUpload::make('image')
                        ->hiddenLabel()
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                        ->directory('images/brands')
                ])->collapsible(),
            ])->columnSpan(['lg' => fn(?Brand $record) => $record === null ? 3 : 2]),

            Group::make()->schema([
                Section::make('Meta Information')->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn(Brand $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn(Brand $record): ?string => $record->updated_at?->diffForHumans()),
                ])->collapsible(),
            ])->columnSpan(['lg' => 1])->hidden(fn(?Brand $record) => $record === null),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->alignCenter()
                    ->defaultImageUrl(url('/images/default.jpg'))
                    ->size(40)
                    ->toggleable(),

                TextColumn::make('code')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
                        if ($record->products()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Brand is in use')
                                ->body('Brand is exist on products.')
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
        return [ProductsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
