<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * A data object that represents an object that has been updated. This is the root object expected
 * to be passed into the Search UpdateWorker.
 */
final class ObjectUpdated
{
    /**
     * An array of properties on the object that have changed. If the object is new, or the changed
     * properties are unknown, this can be set to null. When set to null, any subscriptions for this
     * class type will all receive the update without checking for property matches.
     *
     * @var string[]|null
     */
    private $changedProperties;

    /**
     * The class of the object that has changed.
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
     * @param string $class
     * @param mixed[] $ids
     * @param null|string[] $changedProperties
     */
    public function __construct(string $class, array $ids, ?array $changedProperties = null)
    {
        $this->class = $class;
        $this->ids = $ids;
        $this->changedProperties = $changedProperties;
    }

    /**
     * Returns the changed properties.
     *
     * @return null|string[]
     */
    public function getChangedProperties(): ?array
    {
        return $this->changedProperties;
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
     * Returns the object's ids.
     *
     * @return mixed[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
