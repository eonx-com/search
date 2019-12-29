<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

final class EntityUpdateListener implements ShouldQueue
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
     * @param \EonX\EasyEntityChange\Events\EntityChangeEvent $event
     *
     * @return void
     */
    public function handle(EntityChangeEvent $event): void
    {
        $this->worker->handle($event->getChanges());
    }
}
