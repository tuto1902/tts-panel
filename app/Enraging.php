<?php

namespace App;

use Illuminate\Support\Collection;

class Enraging
{
    public static function quote()
    {
        return static::quotes()->random();
    }

    public static function quotes(): Collection
    {
        return collect([
            'deployed to production without testing!',
            'pushed directly to main branch!',
            'pushed to production on a Friday!',
            'committed straight to main without a single comment!',
            'just wrote a SQL query without a WHERE clause!',
            'hardcoded API keys and pushed to GitHub!',
            'deployed with a "temporary fix" that became permanent!',
            'just approved their own pull request!',
            'fixed a bug by commenting out the error message!',
            'just force-pushed to main and said, "It works on my machine!"'
        ]);
    }
}
