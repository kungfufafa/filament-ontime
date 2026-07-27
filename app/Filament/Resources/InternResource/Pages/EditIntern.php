<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Filament\Resources\InternResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EditIntern extends EditRecord
{
    protected static string $resource = InternResource::class;

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
                ->modalDescription('Akun user akan dibuat otomatis menggunakan data nama dan email/NIS magang.')
                ->action(function () {
                    $record = $this->record;
                    $email = $record->email ?: strtolower($record->nis).'@magang.local';

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

                    if ($role = Role::firstOrCreate(['name' => 'Intern'])) {
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
