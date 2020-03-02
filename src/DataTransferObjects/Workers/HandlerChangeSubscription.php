<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Workers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;

/**
 * This is an internal DTO used to keep track of subscriptions and which handler has that
 * subscription.
 *
 * It is only used inside the UpdateWorker, and should not be used anywhere else.
 *
 * @internal
 */
final class HandlerChangeSubscription
{
    /**
     * @var string
     */
    private $handlerKey;

    /**
     * @phpstan-var \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription<mixed>
     *
     * @var \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription
     */
    private $subscription;

    /**
     * Constructor.
     *
     * @phpstan-param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription<mixed> $subscription
     *
     * @param string $handlerKey
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription $subscription
     */
    public function __construct(string $handlerKey, ChangeSubscription $subscription)
    {
        $this->subscription = $subscription;
        $this->handlerKey = $handlerKey;
    }

    /**
     * Returns the handler key.
     *
     * @return string
     */
    public function getHandlerKey(): string
    {
        return $this->handlerKey;
    }

    /**
     * Returns the change subscription.
     *
     * @phpstan-return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription<mixed>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription
     */
    public function getSubscription(): ChangeSubscription
    {
        return $this->subscription;
    }
}
