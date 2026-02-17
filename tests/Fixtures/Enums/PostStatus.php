<?php

declare(strict_types=1);

namespace Frolax\Typescript\Tests\Fixtures\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
