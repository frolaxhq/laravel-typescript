<?php

declare(strict_types=1);

namespace Frolax\Typescript\Data;

/**
 * Definition of a model accessor/mutator.
 */
final readonly class AccessorDefinition
{
    public function __construct(
        public string $name,
        /** 'traditional' (getXAttribute) or 'attribute' (new Attribute class) */
        public string $style,
        /** PHP return type name if detectable */
        public ?string $returnType = null,
        public bool $isNullable = false,
        /** FQCN of the enum class if the accessor returns an enum */
        public ?string $enumClass = null,
    ) {}
}
