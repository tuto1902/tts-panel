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
                <audio controls>
                    <source src="/storage/{{ $event->file_path }}" type="audio/mpeg">
                    Your browser does not support the audio tag.
                </audio>
            </div>
            @if($event->played_at == null)
            <x-button wire:click="$parent.markAsPlayed({{ $event->id }})">Mark As Played</x-button>
            @endif
        </div>
    </div>
</div>
