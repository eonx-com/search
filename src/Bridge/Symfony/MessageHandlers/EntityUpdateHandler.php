<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EntityUpdateHandler implements MessageHandlerInterface
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
    public function __invoke(EntityChangeEvent $event): void
    {
        $this->worker->handle($event->getChanges());
    }
}
