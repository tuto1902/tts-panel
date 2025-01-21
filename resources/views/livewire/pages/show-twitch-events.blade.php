
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        TTS Queue
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" wire:poll>
    @foreach ($this->events as $event)
        <livewire:twitch-event-card wire:key="{{ $event->id }}" :event="$event" />
    @endforeach
    </div>
</div>
