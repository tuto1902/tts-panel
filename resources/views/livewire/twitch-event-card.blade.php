<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

        <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
            User Name
        </h1>

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
            <x-button wire:click="markAsPlayed">Mark as Played</x-button>
        </div>
    </div>
</div>
