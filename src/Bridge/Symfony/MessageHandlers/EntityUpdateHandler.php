<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers;

use LoyaltyCorp\Search\Bridge\Symfony\Messages\EntityChangeMessage;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

final class EntityUpdateHandler
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface
     */
    private $worker;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface $worker
     */
    public function __construct(EntityUpdateWorkerInterface $worker)
    {
        $this->worker = $worker;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param \LoyaltyCorp\Search\Bridge\Symfony\Messages\EntityChangeMessage $event
     *
     * @return void
     */
    public function __invoke(EntityChangeMessage $event): void
    {
        $this->worker->handle($event->getChanges());
    }
}
