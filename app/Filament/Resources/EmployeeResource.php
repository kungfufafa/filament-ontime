<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $modelLabel = 'Karyawan';

    protected static ?string $pluralModelLabel = 'Data Karyawan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nip')
                                ->label('NIP / Nomor Induk Pegawai')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),

                            TextInput::make('full_name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255),

                            Select::make('company_id')
                                ->label('Badan Usaha (Company)')
                                ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('division_id', null)),

                            Select::make('division_id')
                                ->label('Divisi')
                                ->options(function (Get $get) {
                                    $companyId = $get('company_id');
                                    if (! $companyId) {
                                        return [];
                                    }

                                    return Division::query()
                                        ->where('company_id', $companyId)
                                        ->where('is_active', true)
                                        ->pluck('name', 'id');
                                })
                                ->required(),

                            Select::make('job_level_id')
                                ->label('Level Jabatan')
                                ->options(JobLevel::query()->orderBy('level_order')->pluck('name', 'id'))
                                ->required(),

                            Select::make('job_title_id')
                                ->label('Posisi / Job Title')
                                ->options(JobTitle::query()->pluck('name', 'id'))
                                ->required(),

                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Telepon')
                                ->tel()
                                ->maxLength(50),

                            DatePicker::make('join_date')
                                ->label('Tanggal Bergabung')
                                ->native(false),

                            Select::make('status')
                                ->label('Status Karyawan')
                                ->options([
                                    'active' => 'Aktif',
                                    'inactive' => 'Non-Aktif',
                                    'resigned' => 'Resign',
                                ])
                                ->default('active')
                                ->required(),

                            Select::make('user_id')
                                ->label('Akun User Sistem (Opsional)')
                                ->placeholder('Pilih Akun User')
                                ->options(User::query()->pluck('name', 'id'))
                                ->searchable()
                                ->nullable(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('division.name')
                    ->label('Divisi')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jobLevel.name')
                    ->label('Level Jabatan')
                    ->sortable(),

                TextColumn::make('jobTitle.name')
                    ->label('Posisi')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'resigned',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Non-Aktif',
                        'resigned' => 'Resign',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Badan Usaha')
                    ->relationship('company', 'name'),

                SelectFilter::make('division_id')
                    ->label('Divisi')
                    ->relationship('division', 'name'),

                SelectFilter::make('job_level_id')
                    ->label('Level Jabatan')
                    ->relationship('jobLevel', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Non-Aktif',
                        'resigned' => 'Resign',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
