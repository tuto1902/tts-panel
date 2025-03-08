<div
    @class([
        'bg-white dark:bg-gray-800 overflow-hidden shadow-xl border-2 dark:border-gray-700 sm:rounded-lg min-w-[40%] mb-6 ml-6 max-w-4xl',
        'hidden' => !$fadeIn && !$fadeOut,
        'animate__animated animate__jackInTheBox' => $fadeIn,
        'animate__animated animate__fadeOutDown' => $fadeOut
    ])
>
    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-start items-center gap-6">
            <img class="size-16 rounded-full object-cover" src="{{ $event?->avatar }}" alt="{{ $event?->nickname }}" />

            <h1 class="mt-8 text-2xl font-extrabold text-gray-900 dark:text-white" style="color: {{ $event?->color }}!important">
                {{ $event?->nickname }}
            </h1>
        </div>

        <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
            {{ $event?->message }}
        </p>
    </div>
</div>

@script
<script>
    let audio = new Audio();
    audio.addEventListener('ended', () => {
        $wire.dispatch('audio-player-ended');
    });

    $wire.on('play-audio', (event) => {
        audio.pause();
        audio.src = `data:audio/mpeg;base64,${event.base64Audio}`;
        audio.play();
    });

    $wire.on('audio-player-ended', () => {
        $wire.markAsPlayed();
    });
</script>
@endscript
