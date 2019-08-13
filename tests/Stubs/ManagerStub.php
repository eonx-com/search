<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ManagerInterface;

/**
 * @coversNothing
 */
class ManagerStub implements ManagerInterface
{
    /**
     * Used to determine how many times `handleUpdates` was called
     *
     * @var int
     */
    private $updateCount = 0;

    /**
     * @var mixed[]
     */
    private $updateObjects;

    /**
     * {@inheritdoc}
     */
    public function getSearchMeta(object $object): array
    {
        return [];
    }

    /**
     * Get the amount of times `handleUpdates` has been called
     *
     * @return int
     */
    public function getUpdateCount(): int
    {
        return $this->updateCount;
    }

    /**
     * Getter for spying on the values passed into handleUpdates
     *
     * @return mixed[]
     */
    public function getUpdateObjects(): array
    {
        return $this->updateObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function handleDeletes(array $ids): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdates(string $class, string $indexSuffix, array $objects): void
    {
        $this->updateCount++;
        $this->updateObjects[] = \compact('class', 'indexSuffix', 'objects');
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchable(string $class): bool
    {
        return true;
    }
}
