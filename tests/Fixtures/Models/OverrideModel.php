<?php

declare(strict_types=1);

namespace Frolax\Typescript\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class OverrideModel extends Model
{
    public array $interfaces = [
        'attachments' => [
            'type' => 'MessagePartAttachment[]',
            'import' => '@/types/ai',
        ],
        'metadata' => [
            'type' => 'MessageMetadata',
            'nullable' => true,
            'import' => '@/types/ai',
        ],
        'logo' => [
            'type' => 'Image',
            'import' => '@/types',
        ],
        'href' => [
            'type' => 'string',
        ],
    ];

    protected function attachmentsSummary(): Attribute
    {
        return Attribute::make(
            get: fn (): array => [],
        );
    }
}
