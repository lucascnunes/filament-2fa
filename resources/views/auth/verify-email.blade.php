<x-filament::layouts.card title="{{__('filament-two-factor-auth::Verify')}}">
    <div class="filament-fortify-verify-email-page">
        <form method="POST" action="{{ route('verification.send') }}" class="space-y-8">

            @csrf
            <x-filament::button type="submit" class="w-full">
                {{ __('filament-two-factor-auth::Verifyl') }}
            </x-filament::button>
        </form>
    </div>
</x-filament::layouts.card>