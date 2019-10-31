<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Listeners;

use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\Search\Workers\EntityDeleteDataWorker;

final class EntityDeleteDataListener
{
    /**
     * @var \LoyaltyCorp\Search\Workers\EntityDeleteDataWorker
     */
    private $worker;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Workers\EntityDeleteDataWorker $worker
     */
    public function __construct(EntityDeleteDataWorker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * The EntityDeleteDataEvent is dispatched synchronously and
     * expects any data to be added to the delete entities to be
     * returned as an array, which will be merged into.
     *
     * @param \LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent $event
     *
     * @return mixed[]
     */
    public function __invoke(EntityDeleteDataEvent $event): array
    {
        return $this->worker->handle($event->getDeletes());
    }
}
