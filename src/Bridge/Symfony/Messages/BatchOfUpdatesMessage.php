<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Messages;

use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\SearchMessageInterface;

final class BatchOfUpdatesMessage implements SearchMessageInterface
{
    /**
     * Stores an index suffix if one is to be used during batch processing.
     *
     * @var string
     */
    private $indexSuffix;

    /**
     * @var iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    private $updates;

    /**
     * BatchOfUpdates constructor.
     *
     * @param string $indexSuffix
     * @param iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     */
    public function __construct(string $indexSuffix, iterable $updates)
    {
        $this->indexSuffix = $indexSuffix;
        $this->updates = $updates;
    }

    /**
     * Returns the index suffix.
     *
     * @return string
     */
    public function getIndexSuffix(): string
    {
        return $this->indexSuffix;
    }

    /**
     * @return iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    public function getUpdates(): iterable
    {
        return $this->updates;
    }
}
