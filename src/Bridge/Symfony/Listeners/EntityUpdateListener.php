<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Listeners;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Symfony\Messages\EntityChangeMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class EntityUpdateListener
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $messageBus;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Messenger\MessageBusInterface $messageBus
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
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
        $this->messageBus->dispatch(new EntityChangeMessage($event->getChanges()));
    }
}
