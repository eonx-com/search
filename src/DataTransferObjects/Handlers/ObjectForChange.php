<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This is a DTO used by the search system to keep track of objects (and their ids) that should be
 * updated and which handler will receive the DTO.
 *
 * The object is serialised into an async queue message to be actioned by a worker process.
 *
 * @template T
 */
abstract class ObjectForChange
{
    /**
     * The class of the object that has changed.
     *
     * @phpstan-var class-string<T>
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
     * If the object has been resolved during the search process this property will hold
     * a reference to the object that this change represents.
     *
     * @phpstan-var T
     *
     * @var object|null
     */
    private $object;

    /**
     * Constructor.
     *
     * @phpstan-param class-string<T> $class
     * @phpstan-param T|null $object
     *
     * @param string $class
     * @param mixed[] $ids
     * @param object|null $object
     */
    public function __construct(string $class, array $ids, ?object $object = null)
    {
        $this->class = $class;
        $this->ids = $ids;
        $this->object = $object;
    }

    /**
     * Returns the changed objects class.
     *
     * @phpstan-return class-string<T>
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

    /**
     * Returns a reference to the object if one has already been looked up.
     *
     * @phpstan-return T|null
     *
     * @return null|object
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * Sets the object for this change.
     *
     * @phpstan-param T $object
     *
     * @param object $object
     *
     * @return void
     */
    public function setObject(object $object): void
    {
        $this->object = $object;
    }
}
