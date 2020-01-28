<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This object represents and is used for a Search Handler to indicate an interest in a specific
 * object type that it will react to to update a search index.
 *
 * It should at a minimum specify the class that it should be notified about and and array of
 * properties that it cares about. Note however, the specific properties that were updated are
 * sometimes not available and the handler may be notified of an update where the changed
 * properties are unknown.
 *
 * A transformation callback can be optionally provided that will receive the ChangedEntity DTO
 * that matches the class/properties and is expected to return an array of ObjectForChange DTOs
 * that will be passed into the SearchHandler's retrieveObjects method.
 *
 * @template T
 */
final class ChangeSubscription
{
    /**
     * The class that we are listening for changes to.
     *
     * @phpstan-var class-string<T>
     *
     * @var string
     */
    private $class;

    /**
     * An array of properties we would like to react to.
     *
     * @var string[]|null
     */
    private $properties;

    /**
     * If provided, a callable that will be used to transform the ObjectUpdated DTO into an array
     * of ObjectForUpdate DTOs.
     *
     * phpcs:disable
     *
     * @phpstan-var null|callable(\EonX\EasyEntityChange\DataTransferObjects\ChangedEntity): array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>>
     *
     * @var callable|null
     *
     * phpcs:enable
     */
    private $transform;

    /**
     * Constructor.
     *
     * phpcs:disable
     * @phpstan-param class-string<T> $class
     * @phpstan-param callable(\EonX\EasyEntityChange\DataTransferObjects\ChangedEntity): array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>> $transform
     *
     * @param string $class
     * @param string[]|null $properties
     * @param callable|null $transform
     * phpcs:enable
     */
    public function __construct(string $class, ?array $properties = null, ?callable $transform = null)
    {
        $this->class = $class;
        $this->properties = $properties;
        $this->transform = $transform;
    }

    /**
     * Return class.
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
     * Return properties.
     *
     * @return string[]|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * Return transform callable.
     *
     * phpcs:disable
     *
     * @phpstan-return null|callable(\EonX\EasyEntityChange\DataTransferObjects\ChangedEntity): array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>>
     *
     * @return callable|null
     *
     * phpcs:enable
     */
    public function getTransform(): ?callable
    {
        return $this->transform;
    }
}
