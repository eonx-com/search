<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Workers\EntityDeleteWorker;

final class EntityDeleteListener implements ShouldQueue
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
        /**
         * @var array<string, array<string>> $search
         *
         * The search key on deletes, if it exists is a multi dimensional string
         * array created by the EntityDeleteDataListener.
         */
        $search = $event->getDeletes()['search'] ?? [];

        $this->worker->handle($search);
    }
}
