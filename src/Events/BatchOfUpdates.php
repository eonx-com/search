<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Events;

final class BatchOfUpdates
{
    /**
     * @var iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    private $updates;

    /**
     * BatchOfUpdates constructor.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     */
    public function __construct(iterable $updates)
    {
        $this->updates = $updates;
    }

    /**
     * @return iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    public function getUpdates(): iterable
    {
        return $this->updates;
    }
}
