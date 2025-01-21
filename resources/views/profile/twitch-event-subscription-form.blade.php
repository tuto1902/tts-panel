<x-action-section>
    <x-slot name="title">
        {{ __('Twitch Event Subscription') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Subscribe to specific Twitch events') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            @if ($this->verificationPending)
                {{ __('The event subscription needs to be verified first.') }}
            @elseif($this->enabled)
                {{ __('You are subscribed to Twitch events.') }}
            @else
                {{ __('You have not subscribed to any Twitch events.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
            <p>
                {{ __('When subscribed to Twitch events, the webhook url will be called every time that event happens.') }}
            </p>
        </div>

        <div class="mt-5">
            @if (!$this->enabled && !$this->verificationPending)
                <x-confirms-password wire:then="enableTwitchEventSubscription">
                    <x-button type="button" wire:loading.attr="disabled" data-test-connect>
                        {{ __('Subscribe') }}
                    </x-button>
                </x-confirms-password>
            @endif
        </div>
    </x-slot>
</x-action-section>
