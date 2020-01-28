<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Events;

final class BatchOfUpdates
{
    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]|iterable
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
     * @return \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]|iterable
     */
    public function getUpdates(): iterable
    {
        return $this->updates;
    }
}
