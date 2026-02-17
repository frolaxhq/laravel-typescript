<?php

namespace Frolax\Typescript\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Frolax\Typescript\Typescript
 */
class Typescript extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Frolax\Typescript\Typescript::class;
    }
}
