<?php

declare(strict_types=1);

use SoloTerm\Solo\Commands\Command;
use SoloTerm\Solo\Commands\EnhancedTailCommand;
use SoloTerm\Solo\Commands\MakeCommand;
use SoloTerm\Solo\Hotkeys as Hotkeys;
use SoloTerm\Solo\Themes as Themes;

// Solo may not (should not!) exist in prod, so we have to
// check here first to see if it's installed.
if (! class_exists('\SoloTerm\Solo\Manager')) {
    return [
        //
    ];
}

return [
    /*
    |--------------------------------------------------------------------------
    | Themes
    |--------------------------------------------------------------------------
    */
    'theme' => env('SOLO_THEME', 'dark'),

    'themes' => [
        'light' => Themes\LightTheme::class,
        'dark' => Themes\DarkTheme::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Keybindings
    |--------------------------------------------------------------------------
    */
    'keybinding' => env('SOLO_KEYBINDING', 'default'),

    'keybindings' => [
        'default' => Hotkeys\DefaultHotkeys::class,
        'vim' => Hotkeys\VimHotkeys::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    */
    'commands' => [
        'About' => 'php artisan solo:about',
        'Logs' => EnhancedTailCommand::file(storage_path('logs/laravel.log')),
        'Vite' => 'npm run dev',
        'Make' => new MakeCommand,
        // 'HTTP' => 'php artisan serve',

        // Lazy commands do no automatically start when Solo starts.
        'Dumps' => Command::from('php artisan solo:dumps')->lazy(),
        'Reverb' => Command::from('php artisan reverb:start --debug --host=0.0.0.0')->lazy(),
        'Pint' => Command::from('./vendor/bin/pint --ansi')->lazy(),
        'Queue' => Command::from('php artisan queue:listen')->lazy(),
        'Tests' => Command::from('composer test')->lazy(),
        'Lint' => Command::from('composer lint')->lazy(),
        'Refactor' => Command::from('composer refactor')->lazy(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Miscellaneous
    |--------------------------------------------------------------------------
    */

    /*
     * If you run the solo:dumps command, Solo will start a server to receive
     * the dumps. This is the address. You probably don't need to change
     * this unless the default is already taken for some reason.
     */
    'dump_server_host' => env('SOLO_DUMP_SERVER_HOST', 'tcp://127.0.0.1:9984'),
];
