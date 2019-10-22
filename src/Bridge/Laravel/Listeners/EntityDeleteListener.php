<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Workers\EntityDeleteWorker;

final class EntityDeleteListener
{
    /**
     * @var \LoyaltyCorp\Search\Workers\EntityDeleteWorker
     */
    private $worker;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Workers\EntityDeleteWorker $worker
     */
    public function __construct(EntityDeleteWorker $worker)
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
        $this->worker->handle($event->getDeletes()['search'] ?? []);
    }
}
