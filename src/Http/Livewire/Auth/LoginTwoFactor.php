<?php

namespace Vormkracht10\TwoFactorAuth\Http\Livewire\Auth;

use App\Models\User;
use App\Notifications\SendOTP;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class LoginTwoFactor extends Page implements HasActions, HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;
    use WithRateLimiting;

    protected static string $layout = 'filament-two-factor-auth::layouts.login';

    protected static string $view = 'filament-two-factor-auth::auth.login-two-factor';

    public ?User $challengedUser = null;

    public function mount(TwoFactorLoginRequest $request): void
    {
        if ($request->challengedUser()) {
            $this->challengedUser = $request->challengedUser();
        }

        $this->form->fill();
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function resend(): ?Action
    {
        return Action::make('resend')
            ->label(__('filament-two-factor-auth::Resend'))
            ->color('info')
            ->extraAttributes(['class' => 'w-full text-xs'])
            ->link()
            ->action(function () {
                if (! $this->throttle()) {
                    return;
                }

                $this->challengedUser->notify(app(SendOTP::class));

                Notification::make()
                    ->title(__('filament-two-factor-auth::Successfully resend the OTP code'))
                    ->success()
                    ->send();
            });
    }

    private function throttle(): bool
    {
        try {
            $this->rateLimit(1);

            return true;
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled') ?: []) ? __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => $exception->minutesUntilAvailable,
                ]) : null)
                ->danger()
                ->send();

            return false;
        }
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('code')
                ->extraInputAttributes(['name' => 'code'])
                ->label(__('filament-two-factor-auth::Code')),
            TextInput::make('recovery_code')
                ->extraInputAttributes(['name' => 'recovery_code'])
                ->label(__('filament-two-factor-auth::Recovery code')),

        ];
    }
}