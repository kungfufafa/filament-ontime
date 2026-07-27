<?php

namespace App\Filament\Resources\FreelanceResource\Pages;

use App\Filament\Resources\FreelanceResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EditFreelancer extends EditRecord
{
    protected static string $resource = FreelanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createUser')
                ->label('Buat Akun User')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->visible(fn (): bool => ! $this->record->user_id)
                ->requiresConfirmation()
                ->modalHeading("Buat Akun User untuk {$this->record->full_name}")
                ->modalDescription('Akun user akan dibuat otomatis menggunakan data nama dan email/ID freelancer.')
                ->action(function () {
                    $record = $this->record;
                    $email = $record->email ?: strtolower($record->freelancer_number).'@freelance.local';

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
                        'password' => Hash::make('password123'),
                    ]);

                    if ($role = Role::where('name', 'Employee')->first()) {
                        $user->assignRole($role);
                    }

                    $record->update(['user_id' => $user->id]);

                    Notification::make()
                        ->title('Akun User Berhasil Dibuat')
                        ->body("Akun untuk {$record->full_name} berhasil dibuat.\nEmail: {$email} | Password: password123")
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
