<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FreelanceResource\Pages;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use UnitEnum;

class FreelanceResource extends Resource
{
    protected static ?string $model = Freelancer::class;

    protected static ?string $modelLabel = 'Freelance';

    protected static ?string $pluralModelLabel = 'Data Freelance';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen SDM';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Freelance')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('freelancer_number')
                                ->label('ID / Nomor Freelance')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),

                            TextInput::make('full_name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('institution')
                                ->label('Keahlian / Agensi / Vendor (Opsional)')
                                ->nullable()
                                ->maxLength(255),

                            Select::make('company_id')
                                ->label('Badan Usaha (Company)')
                                ->options(Company::query()->where('is_active', true)->pluck('name', 'id'))
                                ->helperText('Otomatis terisi jika PIC / Supervisor Karyawan dipilih.')
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
                                ->helperText('Otomatis terisi jika PIC / Supervisor Karyawan dipilih.')
                                ->required(),

                            Select::make('supervisor_id')
                                ->label('Penanggung Jawab / PIC (Karyawan)')
                                ->relationship('supervisor', 'full_name', function ($query, Get $get) {
                                    $query->whereNotIn('status', ['inactive', 'resigned']);
                                    if ($companyId = $get('company_id')) {
                                        $query->where('company_id', $companyId);
                                    }
                                })
                                ->getOptionLabelFromRecordUsing(fn (Employee $record) => "{$record->nip} - {$record->full_name}")
                                ->helperText('Memilih PIC / Supervisor akan otomatis mengeset Badan Usaha & Divisi.')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    if (! $state) {
                                        return;
                                    }

                                    $employee = Employee::find($state);
                                    if ($employee) {
                                        $set('company_id', $employee->company_id);
                                        $set('division_id', $employee->division_id);
                                    }
                                })
                                ->nullable(),

                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Telepon')
                                ->tel()
                                ->maxLength(50),

                            DatePicker::make('start_date')
                                ->label('Tanggal Mulai Kontrak')
                                ->native(false),

                            DatePicker::make('end_date')
                                ->label('Tanggal Selesai Kontrak')
                                ->native(false),

                            Select::make('status')
                                ->label('Status Freelance')
                                ->options([
                                    'active' => 'Aktif',
                                    'completed' => 'Selesai Kontrak',
                                    'extended' => 'Diperpanjang',
                                    'terminated' => 'Berhenti',
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
                TextColumn::make('freelancer_number')
                    ->label('ID / No. Freelance')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('institution')
                    ->label('Keahlian / Agensi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('division.name')
                    ->label('Divisi')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supervisor.full_name')
                    ->label('PIC / Supervisor')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'warning' => 'extended',
                        'danger' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Aktif',
                        'completed' => 'Selesai Kontrak',
                        'extended' => 'Diperpanjang',
                        'terminated' => 'Berhenti',
                        default => $state,
                    }),

                TextColumn::make('user.email')
                    ->label('Akun User')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Sudah Punya Akun' : 'Belum Ada Akun')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Badan Usaha')
                    ->relationship('company', 'name'),

                SelectFilter::make('division_id')
                    ->label('Divisi')
                    ->relationship('division', 'name'),

                SelectFilter::make('supervisor_id')
                    ->label('PIC / Supervisor (Karyawan)')
                    ->relationship('supervisor', 'full_name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai Kontrak',
                        'extended' => 'Diperpanjang',
                        'terminated' => 'Berhenti',
                    ]),
            ])
            ->actions([
                Action::make('createUser')
                    ->label('Buat Akun')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Freelancer $record): bool => ! $record->user_id)
                    ->modalHeading(fn (Freelancer $record) => "Buat Akun User untuk {$record->full_name}")
                    ->modalDescription('Tinjau kredensial yang akan dibuat untuk akun pekerja freelance ini.')
                    ->schema([
                        TextInput::make('email')
                            ->label('Alamat Email Login')
                            ->email()
                            ->default(fn (Freelancer $record) => $record->email ?: strtolower($record->freelancer_number).'@freelance.local')
                            ->required(),
                        TextInput::make('password')
                            ->label('Password Awal')
                            ->default('password123')
                            ->required(),
                    ])
                    ->action(function (Freelancer $record, array $data) {
                        $email = $data['email'];

                        if (User::where('email', $email)->exists()) {
                            Notification::make()
                                ->title('Gagal Membuat Akun')
                                ->body("Email {$email} sudah digunakan oleh akun user lain.")
                                ->danger()
                                ->send();

                            return;
                        }

                        $user = User::create([
                            'name' => $record->full_name,
                            'email' => $email,
                            'password' => Hash::make($data['password']),
                        ]);

                        if ($role = Role::where('name', 'Employee')->first()) {
                            $user->assignRole($role);
                        }

                        $record->update(['user_id' => $user->id]);

                        Notification::make()
                            ->title('Akun User Berhasil Dibuat!')
                            ->body("Kredensial Login {$record->full_name}:\nEmail: {$email}\nPassword: {$data['password']}")
                            ->success()
                            ->send();
                    }),

                Action::make('cetakSurat')
                    ->label('Cetak Surat')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->modalHeading('Surat Keterangan Kerjasama Freelance')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Freelancer $record) => view('filament.modals.print-freelance', ['record' => $record])),

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
            'index' => Pages\ListFreelancers::route('/'),
            'create' => Pages\CreateFreelancer::route('/create'),
            'edit' => Pages\EditFreelancer::route('/{record}/edit'),
        ];
    }
}
