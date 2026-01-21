<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Staff';
    protected static ?string $navigationLabel = 'Clinic Team';
    protected static ?int $navigationSort = 7;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Link a system user to a health facility role.' : null)
                    ->schema([

                        Forms\Components\Select::make('user_id')
                            ->label('Staff User')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                $query
                                    ->where('organization_id', auth()->user()->organization_id)
                                    ->whereDoesntHave('roles', function ($q) {
                                        $q->where('name', 'super_admin');
                                    })
                                    ->whereDoesntHave('doctor') // exclude users already linked as staff
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Select a system user')
                            ->visibleOn('create')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'User must already exist in the system' : null),



                        Forms\Components\Select::make('specialty')
                            ->label('Specialty / Role')
                            ->options([
                                'doctor' => 'Doctor',
                                'nurse' => 'Nurse',
                                'midwife' => 'Midwife',
                                'clinical_officer' => 'Clinical Officer',
                                'lab_technician' => 'Lab Technician',
                                'pharmacist' => 'Pharmacist',
                                'radiographer' => 'Radiographer',
                                'receptionist' => 'Receptionist',
                                'admin' => 'Administrator',
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder('Select specialty'),

                        Forms\Components\TextInput::make('license_number')
                            ->label('License / Registration Number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. MD-12345')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Issued by the professional regulatory body' : null),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Working Schedule')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Define when the staff member is available.' : null)
                    ->schema([

                        Forms\Components\KeyValue::make('working_hours_json')
                            ->label('Working Hours')
                            ->keyLabel('Day')
                            ->valueLabel('Hours')
                            ->addButtonLabel('Add Day')

                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Example: Monday → 08:00 - 17:00' : null),

                    ])
                    ->columnSpanFull(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Member Entity')
                    ->placeholder("---")
                    ->numeric(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Fullname')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->placeholder("---")
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'view' => Pages\ViewDoctor::route('/{record}'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
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
