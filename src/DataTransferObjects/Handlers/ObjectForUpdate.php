<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This is a DTO used by the search system to keep track of objects (and their ids) that should be
 * updated and which handler will receive the DTO.
 *
 * The object is serialised into an async queue message to be actioned by a worker process.
 */
final class ObjectForUpdate
{
    /**
     * The class of the object that has changed.
     *
     * @phpstan-var class-string
     *
     * @var string
     */
    private $class;

    /**
     * An array of ids that represent the object, typically its primary key or a composite key.
     *
     * @var mixed[]
     */
    private $ids;

    /**
     * Constructor
     *
     * @phpstan-param class-string $class
     *
     * @param string $class
     * @param mixed[] $ids
     */
    public function __construct(string $class, array $ids)
    {
        $this->class = $class;
        $this->ids = $ids;
    }

    /**
     * Returns the changed objects class.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Returns the object's ids.
     *
     * @return mixed[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
