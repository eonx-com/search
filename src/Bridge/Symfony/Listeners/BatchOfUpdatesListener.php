<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Listeners;

use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class BatchOfUpdatesListener
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $messageBus;

    /**
     * BatchOfUpdatesListener constructor.
     *
     * @param \Symfony\Component\Messenger\MessageBusInterface $messageBus
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Handles batch of updates.
     *
     * @param \LoyaltyCorp\Search\Events\BatchOfUpdatesEvent $batchOfUpdates
     *
     * @return void
     */
    public function __invoke(BatchOfUpdatesEvent $batchOfUpdates): void
    {
        $this->messageBus->dispatch($batchOfUpdates);
    }
}
