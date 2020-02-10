<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use Eonx\TestUtils\Stubs\BaseStub;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @coversNothing
 */
final class EventDispatcherStub extends BaseStub implements EventDispatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($event)
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());

        return $event;
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
}
