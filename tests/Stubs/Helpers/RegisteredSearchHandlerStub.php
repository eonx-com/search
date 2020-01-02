<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
final class RegisteredSearchHandlerStub extends BaseStub implements RegisteredSearchHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * Returns calls to getTransformableHandlersByKey.
     *
     * @return mixed[]
     */
    public function getGetTransformableHandlersByKeyCalls(): array
    {
        return $this->getCalls(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionsGroupedByClass(): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlerByKey(string $key): TransformableSearchHandlerInterface
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());

        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlers(): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }
}
