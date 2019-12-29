<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ManagerInterface;

/**
 * @coversNothing
 */
final class ManagerStub implements ManagerInterface
{
    /**
     * @var mixed[][]
     */
    private $deletes = [];

    /**
     * Used to determine how many times `handleUpdates` was called.
     *
     * @var int
     */
    private $updateCount = 0;

    /**
     * @var mixed[]
     */
    private $updateObjects;

    /**
     * Getter for spying on the values passed into handleUpdates.
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
        $this->deletes[] = $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdates(string $class, string $indexSuffix, array $objects): void
    {
        $this->updateCount++;
        $this->updateObjects[] = \compact('class', 'indexSuffix', 'objects');
    }
}
