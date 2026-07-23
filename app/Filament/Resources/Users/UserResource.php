<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Company;
use App\Models\Division;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static ?string $modelLabel = 'Pengguna (User)';

    protected static ?string $pluralModelLabel = 'Manajemen User';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun User')
                    ->description('Buat dan atur kredensial akun pengguna serta peran (Role) aksesnya.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Akun User')
                                ->required()
                                ->live(onBlur: true)
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('Alamat Email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                                ->dehydrated(fn (?string $state) => filled($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->maxLength(255),

                            Select::make('roles')
                                ->label('Role Akses')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->required(),
                        ]),
                    ]),

                Section::make('Profil & Relasi Data Karyawan (Employee Profile)')
                    ->description('Tautkan atau buat data Karyawan (Badan Usaha, Divisi, Jabatan, NIP, dll.) untuk user ini.')
                    ->relationship('employee')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('company_id')
                                ->label('Badan Usaha (Company)')
                                ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('division_id', null)),

                            Select::make('division_id')
                                ->label('Divisi Target')
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
                                ->label('Tingkat Jabatan (Job Level)')
                                ->options(JobLevel::query()->orderBy('level_order')->pluck('name', 'id'))
                                ->required(),

                            Select::make('job_title_id')
                                ->label('Nama Jabatan (Job Title)')
                                ->options(JobTitle::query()->pluck('name', 'id'))
                                ->required(),

                            TextInput::make('nip')
                                ->label('NIP Karyawan')
                                ->placeholder('Contoh: EMP-001')
                                ->required(),

                            TextInput::make('full_name')
                                ->label('Nama Lengkap Karyawan')
                                ->required(),

                            DatePicker::make('join_date')
                                ->label('Tanggal Bergabung')
                                ->default(today())
                                ->required(),

                            Select::make('status')
                                ->label('Status Kepegawaian')
                                ->options([
                                    'permanent' => 'Karyawan Tetap (Permanent)',
                                    'contract' => 'Kontrak (Contract)',
                                    'probation' => 'Probation',
                                ])
                                ->default('permanent')
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.company.name')
                    ->label('Badan Usaha')
                    ->placeholder('- Belum Linked -')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employee.division.name')
                    ->label('Divisi')
                    ->placeholder('-')
                    ->sortable(),

                BadgeColumn::make('roles.name')
                    ->label('Role Akses')
                    ->colors([
                        'danger' => 'Superadmin',
                        'warning' => 'Approver',
                        'info' => 'Employee',
                    ]),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
