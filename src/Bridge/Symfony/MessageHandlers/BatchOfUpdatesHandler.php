<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers;

use LoyaltyCorp\Search\Bridge\Symfony\Messages\BatchOfUpdatesMessage;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BatchOfUpdatesHandler implements MessageHandlerInterface
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
     * @param \LoyaltyCorp\Search\Bridge\Symfony\Messages\BatchOfUpdatesMessage $batchOfUpdates
     *
     * @return void
     */
    public function __invoke(BatchOfUpdatesMessage $batchOfUpdates): void
    {
        $this->updateProcessor->process(
            $batchOfUpdates->getIndexSuffix(),
            $batchOfUpdates->getUpdates()
        );
    }
}
