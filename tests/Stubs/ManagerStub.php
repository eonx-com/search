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
     * @var mixed[][]
     */
    private $deletes = [];

    /**
     * @var mixed[]
     */
    private $handlerUpdates = [];

    /**
     * @var mixed[][]
     */
    private $searchMeta = [];

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
     * @param mixed[] $meta
     *
     * @return void
     */
    public function addSearchMeta(array $meta): void
    {
        $this->searchMeta[] = $meta;
    }

    /**
     * Get deletes.
     *
     * @return mixed[][]
     */
    public function getDeletes(): array
    {
        return $this->deletes;
    }

    /**
     * @return mixed[]
     */
    public function getHandlerUpdates(): array
    {
        return $this->handlerUpdates;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchMeta(object $object): array
    {
        return \array_shift($this->searchMeta) ?? [];
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
