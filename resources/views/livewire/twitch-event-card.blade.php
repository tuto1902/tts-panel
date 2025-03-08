<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-start items-center gap-6">
            <img class="size-16 rounded-full object-cover" src="{{ $event->avatar }}" alt="{{ $event->nickname }}" />

            <h1 class="mt-8 text-2xl font-extrabold text-gray-900 dark:text-white" style="color: {{ $event->color }}!important">
                {{ $event->nickname }}
            </h1>
        </div>

        <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
            {{ $event->message }}
        </p>
    </div>
    <div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 p-6 lg:p-8">
        <div class="flex items-center justify-center">
            <div class="flex-1">
                <x-button wire:click="playAudio">Play</x-button>
            </div>
            @if($event->played_at == null)
            <x-button wire:click="$parent.markAsPlayed({{ $event->id }})">Mark As Played</x-button>
            @endif
        </div>
    </div>
</div>
@script
<script>
    let audio = new Audio();

    audio.addEventListener('ended', () => {
        $wire.dispatch('audio-ended');
    });

    $wire.on('play-audio', (event) => {
        audio.pause();
        // Change this to a base64 string
        audio.src = `data:audio/mpeg;base64,${event.base64Audio}`;
        audio.play();
    });

    $wire.on('stop-audio', () => {
        audio.pause();
    })
</script>
@endscript
