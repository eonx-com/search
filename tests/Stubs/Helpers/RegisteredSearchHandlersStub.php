<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
final class RegisteredSearchHandlersStub extends BaseStub implements RegisteredSearchHandlersInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars(), []);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionsGroupedByClass(): array
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars(), []);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlerByKey(string $key): TransformableSearchHandlerInterface
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlers(): array
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars(), []);
    }
}
