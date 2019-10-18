<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Workers\EntityUpdateWorker;

final class EntityUpdateListener
{
    /**
     * @var \LoyaltyCorp\Search\Workers\EntityUpdateWorker
     */
    private $worker;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Workers\EntityUpdateWorker $worker
     */
    public function __construct(EntityUpdateWorker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param \LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent $event
     *
     * @return void
     */
    public function handle(EntityChangeEvent $event): void
    {
        $this->worker->handle($event->getUpdates());
    }
}
