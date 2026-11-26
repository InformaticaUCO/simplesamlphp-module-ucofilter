<?php

declare(strict_types=1);

namespace SimpleSAML\Module\ucofilter\ExpressionLanguage;

class SafeAttributes implements \ArrayAccess
{
    /** @var array<string, mixed> */
    private array $attributes;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? [];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
