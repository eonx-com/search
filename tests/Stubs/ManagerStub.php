<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ManagerInterface;

class ManagerStub implements ManagerInterface
{
    /**
     * Used to determine how many times `handleUpdates` was called
     *
     * @var int
     */
    private $updateCount = 0;

    /**
     * {@inheritdoc}
     */
    public function getSearchMeta(object $object): array
    {
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
     * {@inheritdoc}
     */
    public function handleDeletes(array $ids): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdates(string $class, array $ids): void
    {
        $this->updateCount++;
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchable(string $class): bool
    {
    }
}
