<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;

final class UpdateWorker
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface
     */
    private $registeredHandlers;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     */
    public function __construct(RegisteredSearchHandlerInterface $registeredHandlers)
    {
        $this->registeredHandlers = $registeredHandlers;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated[] $changes
     *
     * @return void
     */
    public function handle(array $changes): void
    {
        $updates = $this->gatherUpdates($changes);

        // Break into jobs
    }

    /**
     * Iterates over all updated objects and builds SearchUpdate objects.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated[] $updates
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate[]
     */
    private function gatherUpdates(array $updates): array
    {
        $subscribedUpdates = [];
        $subscriptions = $this->getSubscriptions();

        foreach ($updates as $update) {
            foreach ($subscriptions as $subscription) {
                // If the subscription has no intersection of properties with the update there
                // is nothing further to do.
                if ($this->shouldNotify($subscription->getSubscription(), $update) === false) {
                    continue;
                }

                $transform = $subscription->getSubscription()->getTransform();

                // If we didnt get a callable in the subscription it means that the handler is
                // fine to receive the objects as is.
                if (\is_callable($transform) === false) {
                    $subscribedUpdates[] = [
                        new ObjectForUpdate(
                            $update->getClass(),
                            $subscription->getHandlerKey(),
                            $update->getIds()
                        )
                    ];

                    continue;
                }

                // Otherwise, we need to call the transform callable with the update so it can
                // be converted into updates.
                $subscribedUpdates[$subscription->getHandlerKey()][] = $transform($update);
            }
        }

        if (\count($subscribedUpdates) <= 1) {
            return \reset($subscribedUpdates) ?: [];
        }

        return \array_merge(...$subscribedUpdates);
    }

    /**
     * Retrieve handler subscriptions grouped by class.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription[]
     */
    private function getSubscriptions(): array
    {
        $subscriptions = [];

        foreach ($this->registeredHandlers->getTransformableHandlers() as $handler) {
            foreach ($handler->getSubscriptions() as $subscription) {
                if (\is_array($subscriptions[$subscription->getClass()]) === false) {
                    $subscriptions[$subscription->getClass()] = [];
                }

                $subscriptions[$subscription->getClass()][] = new HandlerChangeSubscription(
                    $handler->getHandlerKey(),
                    $subscription
                );
            }
        }

        return $subscriptions;
    }

    /**
     * Checks if the subscription should be notified of the change.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription $subscription
     * @param \LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated $update
     *
     * @return bool
     */
    private function shouldNotify(ChangeSubscription $subscription, ObjectUpdated $update): bool
    {
        if (\is_array($update->getChangedProperties()) === false) {
            return true;
        }

        $intersection = \array_intersect($subscription->getProperties(), $update->getChangedProperties());

        return \count($intersection) > 0;
    }
}
