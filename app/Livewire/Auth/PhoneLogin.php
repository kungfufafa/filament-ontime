<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\OtpService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Action $loginAction
 * @property-read Schema $form
 */
class PhoneLogin extends SimplePage
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public bool $awaitingOtp = false;

    public function mount(): void
    {
        if (! config('services.gateway_hub.enabled')) {
            abort(404, 'Fitur login WhatsApp belum diaktifkan.');
        }

        if (Filament::getCurrentPanel() === null) {
            Filament::setCurrentPanel(Filament::getPanel('admin'));
            Filament::bootCurrentPanel();
        }

        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->maxWidth = 'full';
        $this->form->fill();
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    public function send(OtpService $otpService): void
    {
        $data = $this->form->getState();
        $rawPhone = $data['whatsapp_number'] ?? null;
        $normalized = $this->normalizePhone(is_string($rawPhone) ? $rawPhone : null);

        if (! $normalized) {
            throw ValidationException::withMessages([
                'data.whatsapp_number' => 'Nomor WhatsApp tidak valid.',
            ]);
        }

        $rateKey = 'whatsapp-otp:filament:'.md5($normalized).':'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages([
                'data.whatsapp_number' => 'Terlalu banyak permintaan OTP. Coba lagi sebentar lagi.',
            ]);
        }

        RateLimiter::hit($rateKey, 300);

        $user = User::findByPhone($normalized);

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'data.whatsapp_number' => 'Nomor WhatsApp belum terdaftar atau akun tidak aktif.',
            ]);
        }

        $result = $otpService->requestOtp($normalized);

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'data.whatsapp_number' => $result['message'],
            ]);
        }

        $this->awaitingOtp = true;
        $this->form->fill([
            'whatsapp_number' => $normalized,
            'otp' => null,
        ]);
    }

    public function verify(OtpService $otpService): void
    {
        $data = $this->form->getState();
        $rawPhone = $data['whatsapp_number'] ?? null;
        $normalized = $this->normalizePhone(is_string($rawPhone) ? $rawPhone : null);
        $otp = (string) ($data['otp'] ?? '');

        if (! $normalized) {
            throw ValidationException::withMessages([
                'data.whatsapp_number' => 'Nomor WhatsApp tidak valid.',
            ]);
        }

        $user = $otpService->verifyOtp($normalized, $otp);

        if (! $user) {
            throw ValidationException::withMessages([
                'data.otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        Auth::login($user, true);
        session()->regenerate();

        $this->redirect(config('filament.home_url', '/admin'));
    }

    public function changePhone(): void
    {
        $this->awaitingOtp = false;
        $this->form->fill();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('whatsapp_number')
                ->label('Nomor WhatsApp')
                ->tel()
                ->required()
                ->maxLength(30)
                ->autocomplete('tel')
                ->disabled(fn (): bool => $this->awaitingOtp)
                ->dehydrated()
                ->autofocus(),
            TextInput::make('otp')
                ->label('Kode OTP')
                ->helperText('Masukkan 6 digit kode yang dikirim ke WhatsApp.')
                ->required()
                ->numeric()
                ->length(6)
                ->autocomplete('one-time-code')
                ->autofocus()
                ->visible(fn (): bool => $this->awaitingOtp),
        ]);
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Kembali ke halaman masuk')
            ->icon(match (__('filament-panels::layout.direction')) {
                'rtl' => FilamentIcon::resolve(PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN_RTL) ?? Heroicon::ArrowRight,
                default => FilamentIcon::resolve(PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN) ?? Heroicon::ArrowLeft,
            })
            ->url(filament()->getLoginUrl());
    }

    public function getTitle(): string|Htmlable
    {
        return 'Masuk dengan WhatsApp';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Masuk dengan WhatsApp';
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->awaitingOtp ? $this->getVerifyFormAction() : $this->getSendFormAction(),
        ];
    }

    protected function getSendFormAction(): Action
    {
        return Action::make('send')
            ->label('Kirim OTP WhatsApp')
            ->submit('send');
    }

    protected function getVerifyFormAction(): Action
    {
        return Action::make('verify')
            ->label('Verifikasi dan Masuk')
            ->submit('verify');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->awaitingOtp) {
            return Action::make('changePhone')
                ->link()
                ->label('Ganti nomor atau kirim ulang OTP')
                ->action('changePhone');
        }

        if (! filament()->hasLogin()) {
            return null;
        }

        return $this->loginAction;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler(fn (): string => $this->awaitingOtp ? 'verify' : 'send')
                ->footer([
                    Actions::make($this->getFormActions())
                        ->alignment($this->getFormActionsAlignment())
                        ->fullWidth($this->hasFullWidthFormActions())
                        ->key('form-actions'),
                ]),
        ]);
    }

    public function getView(): string
    {
        return 'filament.auth.phone-login';
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
