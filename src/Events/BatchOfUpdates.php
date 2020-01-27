<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Events;

final class BatchOfUpdates
{
    /**
     * @var array|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    private $updates;

    /**
     * BatchOfUpdates constructor.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     */
    public function __construct(array $updates)
    {
        $this->updates = $updates;
    }

    /**
     * @return array|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }
}
