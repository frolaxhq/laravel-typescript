<?php

declare(strict_types=1);

namespace Frolax\Typescript\Tests\Fixtures\Enums;

enum UserRole: string
{
    /** The administrator role */
    case Admin = 'admin';
    case Editor = 'editor';
    case User = 'user';
    case Guest = 'guest';
}
