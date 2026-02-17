<?php

namespace Frolax\Typescript\Commands;

use Illuminate\Console\Command;

class TypescriptCommand extends Command
{
    public $signature = 'laravel-typescript';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
