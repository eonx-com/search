<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Bridge\Symfony;

use Eonx\TestUtils\Stubs\BaseStub;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessageBusStub extends BaseStub implements MessageBusInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($message, array $stamps = []): Envelope
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars(), new Envelope($message));
    }
}
