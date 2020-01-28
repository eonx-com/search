<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use LoyaltyCorp\Search\Events\BatchOfUpdates;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

final class EntityUpdateWorker implements EntityUpdateWorkerInterface
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var \EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface
     */
    private $registeredHandlers;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     * @param \EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface $dispatcher
     * @param int $batchSize
     */
    public function __construct(
        RegisteredSearchHandlerInterface $registeredHandlers,
        EventDispatcherInterface $dispatcher,
        int $batchSize
    ) {
        $this->registeredHandlers = $registeredHandlers;
        $this->dispatcher = $dispatcher;
        $this->batchSize = $batchSize;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[] $changes
     *
     * @return void
     */
    public function handle(array $changes): void
    {
        // If we have no updates, dont bother initialising anything and return early.
        if (\count($changes) === 0) {
            return;
        }

        $updates = $this->gatherUpdates($changes);

        // If we didnt generate any updates, return instead of calling the update processor.
        if (\count($updates) === 0) {
            return;
        }

        $batches = \array_chunk($updates, $this->batchSize);

        foreach ($batches as $batch) {
            $this->dispatcher->dispatch(new BatchOfUpdates($batch));
        }
    }

    /**
     * Takes a change subscription and a ChangedEntity and turns it into an array of ObjectForUpdate
     * DTOs.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription $subscription
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $update
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     */
    private function buildUpdates(HandlerChangeSubscription $subscription, ChangedEntity $update): array
    {
        $transform = $subscription->getSubscription()->getTransform();

        // If we didnt get a callable in the subscription it means that the handler is
        // fine to receive the objects as is.
        if (\is_callable($transform) === false) {
            $classToBuild = $update instanceof DeletedEntity === true
                ? ObjectForDelete::class
                : ObjectForUpdate::class;

            return [
                new $classToBuild(
                    $update->getClass(),
                    $update->getIds()
                ),
            ];
        }

        // Otherwise, we need to call the transform callable with the update so it can
        // be converted into updates.
        return $transform($update);
    }

    /**
     * Iterates over all updated objects and builds SearchUpdate objects.
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[] $changes
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[]
     */
    private function gatherUpdates(array $changes): array
    {
        $subscribedUpdates = [];

        // Retrieves all subscriptions grouped by their subscribing classes
        $subscriptions = $this->registeredHandlers->getSubscriptionsGroupedByClass();

        foreach ($changes as $update) {
            /** @var \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription[] $classSubscriptions */
            $classSubscriptions = $subscriptions[$update->getClass()] ?? [];

            foreach ($classSubscriptions as $subscription) {
                // If the subscription has no intersection of properties with the update there
                // is nothing further to do.
                if ($this->shouldNotify($subscription->getSubscription(), $update) === false) {
                    continue;
                }

                $objectForUpdates = $this->buildUpdates($subscription, $update);

                foreach ($objectForUpdates as $forUpdate) {
                    $subscribedUpdates[] = new HandlerObjectForChange(
                        $subscription->getHandlerKey(),
                        $forUpdate
                    );
                }
            }
        }

        return $subscribedUpdates;
    }

    /**
     * Checks if the subscription should be notified of the change. A subscription will be notified
     * if there is any intersection of changed properties with the subscribed properties array.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription $subscription
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $change
     *
     * @return bool
     */
    private function shouldNotify(ChangeSubscription $subscription, ChangedEntity $change): bool
    {
        // If the properties property on the subscription is null, we always notify
        // of a change.
        if ($subscription->getProperties() === null) {
            return true;
        }

        if ($change instanceof UpdatedEntity === true) {
            $intersection = \array_intersect($subscription->getProperties(), $change->getChangedProperties());

            return \count($intersection) > 0;
        }

        return true;
    }
}
