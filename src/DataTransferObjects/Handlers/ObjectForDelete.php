<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This DTO serves the same purpose as ObjectForUpdate, but indicates a deletion should occur.
 *
 * @template T
 *
 * @extends ObjectForChange<T>
 */
final class ObjectForDelete extends ObjectForChange
{
    /**
     * Stores metadata from the EntityChange system.
     *
     * @var mixed[]
     */
    private $metadata;

    /**
     * Constructor
     *
     * @phpstan-param class-string<T> $class
     *
     * @param string $class
     * @param mixed[] $ids
     * @param mixed[]|null $metadata
     */
    public function __construct(string $class, array $ids, ?array $metadata = null)
    {
        parent::__construct($class, $ids, null);

        $this->metadata = $metadata ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }
}
