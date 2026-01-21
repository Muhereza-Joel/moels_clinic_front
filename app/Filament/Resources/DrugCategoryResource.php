<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrugCategoryResource\Pages;
use App\Filament\Resources\DrugCategoryResource\RelationManagers;
use App\Models\DrugCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrugCategoryResource extends Resource
{
    protected static ?string $model = DrugCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* -------------------------------------------------
             | Basic Information
             | -------------------------------------------------
             */
                Forms\Components\Section::make('Basic Information')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Primary details used to identify this category' : null)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Antibiotics')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Human-readable category name' : null),

                        Forms\Components\TextInput::make('code')
                            ->maxLength(50)
                            ->placeholder('e.g. ANTIB')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Optional short internal code' : null),
                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Hierarchy & Ordering
             | -------------------------------------------------
             */
                Forms\Components\Section::make('Hierarchy & Ordering')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Control category structure and display order' : null)
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('No parent (top-level category)')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select a parent to create a sub-category' : null),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->placeholder('0')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Lower numbers appear first' : null),
                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Description
             | -------------------------------------------------
             */
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull()
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->placeholder('Brief description of this category')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Optional notes for internal use' : null),
                    ]),

                /* -------------------------------------------------
             | Status
             | -------------------------------------------------
             */
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Inactive categories will not be selectable' : null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->placeholder("---")
                    ->label('Parent')
                    ->searchable()
                    ->placeholder('— top level —'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListDrugCategories::route('/'),
            'create' => Pages\CreateDrugCategory::route('/create'),
            'view' => Pages\ViewDrugCategory::route('/{record}'),
            'edit' => Pages\EditDrugCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
