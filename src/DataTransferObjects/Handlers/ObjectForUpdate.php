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
     * @var string
     */
    private $class;

    /**
     * The handler that created the ObjectForUpdate.
     *
     * @var string
     */
    private $handlerKey;

    /**
     * An array of ids that represent the object, typically its primary key or a composite key.
     *
     * @var mixed[]
     */
    private $ids;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $handlerKey
     * @param mixed[] $ids
     */
    public function __construct(string $class, string $handlerKey, array $ids)
    {
        $this->class = $class;
        $this->ids = $ids;
        $this->handlerKey = $handlerKey;
    }

    /**
     * Returns the changed objects class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Returns the handler key.
     *
     * @return string
     */
    public function getHandlerKey(): string
    {
        return $this->handlerKey;
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
