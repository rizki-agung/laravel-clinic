<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PatientAppointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PatientAppointmentResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\PatientAppointmentResource\RelationManagers;
use Illuminate\Support\Facades\Auth;

class PatientAppointmentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PatientAppointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date_of_appointment')
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('prescription')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'jadwal dibuat' => 'Jadwal Dibuat',
                        'selesai periksa' => 'Selesai Periksa',
                        'obat diambil' => 'Obat Diambil',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $isDoctor = Auth::user()->hasRole('Doctor');
                $isPharmacist = Auth::user()->hasRole('Pharmacist');

                if ($isDoctor) {
                    $userId = Auth::user()->id;
                    $query->where('doctor_id', $userId);
                }

                if ($isPharmacist) {
                    $query->where('status', 'selesai periksa')->orWhere('status', 'obat diambil');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_appointment')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPatientAppointments::route('/'),
            'create' => Pages\CreatePatientAppointment::route('/create'),
            'edit' => Pages\EditPatientAppointment::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any'
        ];
    }
}
