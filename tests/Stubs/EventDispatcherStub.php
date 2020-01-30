<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface;
use Eonx\TestUtils\Stubs\BaseStub;

/**
 * @coversNothing
 */
final class EventDispatcherStub extends BaseStub implements EventDispatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($event, $payload = null, ?bool $halt = null): ?array
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());

        return [];
    }

    /**
     * Returns calls to dispatch().
     *
     * @return mixed[]
     */
    public function getDispatchCalls(): array
    {
        return $this->getCalls('dispatch');
    }

    /**
     * {@inheritdoc}
     */
    public function listen(array $events, string $listener): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }
}
