<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;

class BatchOfUpdatesListener implements ShouldQueue
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface
     */
    private $updateProcessor;

    /**
     * BatchOfUpdatesListener constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface $updateProcessor
     */
    public function __construct(UpdateProcessorInterface $updateProcessor)
    {
        $this->updateProcessor = $updateProcessor;
    }

    /**
     * Handles batch of updates.
     *
     * @param \LoyaltyCorp\Search\Events\BatchOfUpdatesEvent $batchOfUpdates
     *
     * @return void
     */
    public function handle(BatchOfUpdatesEvent $batchOfUpdates): void
    {
        $this->updateProcessor->process('', $batchOfUpdates->getUpdates());
    }
}
