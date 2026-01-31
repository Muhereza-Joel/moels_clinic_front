<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PdfTemplateResource\Pages\CreatePdfTemplate;
use App\Filament\Resources\PdfTemplateResource\Pages\EditPdfTemplate;
use App\Filament\Resources\PdfTemplateResource\Pages\ListPdfTemplates;
use App\Models\PdfTemplate;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Resources\Pages;
use ValentinMorice\FilamentJsonColumn\JsonColumn;
use ValentinMorice\FilamentJsonColumn\JsonInfolist;
use Illuminate\Support\Facades\File;

class PdfTemplateResource extends Resource
{
    protected static ?string $model = PdfTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'PDF Templates';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Template Info')
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->helperText('Example: diagnosis, prescription, lab_result'),
                    Forms\Components\Toggle::make('active'),
                ]),

            Forms\Components\Section::make('Layout Definition')
                ->schema([
                    JsonColumn::make('layout')
                        ->label('Template Layout')
                        ->helperText('Define sections, grids, and blocks with placeholders like {{patient.name}}'),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
            Tables\Columns\BooleanColumn::make('active'),
            Tables\Columns\TextColumn::make('version'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            JsonInfolist::make('layout')
                ->label('Template Layout'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPdfTemplates::route('/'),
            'create' => CreatePdfTemplate::route('/create'),
            'edit' => EditPdfTemplate::route('/{record}/edit'),
        ];
    }


    /**
     * Pre-populate defaults when creating a new template.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['layout']) && !empty($data['code'])) {
            $path = resource_path("templates/pdf/defaults/{$data['code']}.json");
            if (File::exists($path)) {
                $data['layout'] = json_decode(File::get($path), true);
            }
        }
        return $data;
    }
}
