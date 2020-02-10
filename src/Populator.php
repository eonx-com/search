<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class Populator implements PopulatorInterface
{
    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param \Psr\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        int $batchSize
    ): void {
        $batched = $this->getBatchedIterable($handler, $batchSize);

        foreach ($batched as $batch) {
            $event = new BatchOfUpdatesEvent($indexSuffix, $batch);

            $this->dispatcher->dispatch($event);
        }
    }

    /**
     * Batches a search handler's iterable into batch sizes.
     *
     * @phpstan-param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface<mixed> $handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param int $batchSize
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[][]
     */
    private function getBatchedIterable(TransformableSearchHandlerInterface $handler, int $batchSize): iterable
    {
        $batch = [];

        foreach ($handler->getFillIterable() as $item) {
            $batch[] = new HandlerObjectForChange($handler->getHandlerKey(), $item);

            if (\count($batch) >= $batchSize) {
                yield $batch;

                $batch = [];
            }
        }

        if (\count($batch) > 0) {
            yield $batch;
        }
    }
}
