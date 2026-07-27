<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $modelLabel = 'Badan Usaha';

    protected static ?string $pluralModelLabel = 'Badan Usaha';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Struktur Organisasi';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Perusahaan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Badan Usaha')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label('Kode Perusahaan')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Telepon')
                                ->tel()
                                ->maxLength(50),
                            Textarea::make('address')
                                ->label('Alamat')
                                ->columnSpanFull(),
                            TextInput::make('latitude')
                                ->label('Latitude Koordinat Kantor')
                                ->numeric()
                                ->placeholder('Contoh: -6.2000000'),
                            TextInput::make('longitude')
                                ->label('Longitude Koordinat Kantor')
                                ->numeric()
                                ->placeholder('Contoh: 106.8166667'),
                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon'),
                TextColumn::make('employees_count')
                    ->label('Jumlah Karyawan')
                    ->counts('employees')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Action::make('managePolicy')
                    ->label('Kebijakan Absensi')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->fillForm(fn (Company $record): array => [
                        'late_tolerance_minutes' => $record->policy?->late_tolerance_minutes ?? 15,
                        'require_photo' => $record->policy?->require_photo ?? false,
                        'require_gps' => $record->policy?->require_gps ?? false,
                        'geofence_radius_meters' => $record->policy?->geofence_radius_meters ?? 100,
                        'annual_leave_quota' => $record->policy?->annual_leave_quota ?? 12,
                        'default_approval_stages' => $record->policy?->default_approval_stages ?? 1,
                    ])
                    ->schema([
                        TextInput::make('late_tolerance_minutes')
                            ->label('Toleransi Keterlambatan (Menit)')
                            ->numeric()
                            ->required()
                            ->default(15),

                        Toggle::make('require_photo')
                            ->label('Wajib Foto Absensi')
                            ->default(false),

                        Toggle::make('require_gps')
                            ->label('Wajib Lokasi GPS')
                            ->live()
                            ->default(false),

                        TextInput::make('geofence_radius_meters')
                            ->label('Radius Geofence (Meter)')
                            ->numeric()
                            ->placeholder('Contoh: 100')
                            ->visible(fn (Get $get) => (bool) $get('require_gps'))
                            ->required(fn (Get $get) => (bool) $get('require_gps')),

                        TextInput::make('annual_leave_quota')
                            ->label('Kuota Cuti Tahunan (Hari)')
                            ->numeric()
                            ->required()
                            ->default(12),

                        TextInput::make('default_approval_stages')
                            ->label('Jumlah Tahap Approval Default (Referensi)')
                            ->numeric()
                            ->required()
                            ->default(1),
                    ])
                    ->action(function (Company $record, array $data): void {
                        $record->policy()->updateOrCreate(
                            ['company_id' => $record->id],
                            $data
                        );
                    }),

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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
