<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;

final class Populator implements PopulatorInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface
     */
    private $nameTransformer;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface $nameTransformer
     */
    public function __construct(
        ClientInterface $client,
        IndexNameTransformerInterface $nameTransformer
    ) {
        $this->client = $client;
        $this->nameTransformer = $nameTransformer;
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
            $this->populateWith($handler, $indexSuffix, $batch);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function populateWith(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        iterable $objects
    ): void {
        $actions = [];

        foreach ($objects as $object) {
            $update = $handler->transform($object);

            // The handler didnt return an action to perform.
            if (($update instanceof DocumentAction) === false) {
                continue;
            }

            $index = $this->nameTransformer->transformIndexName($handler, $object) . $indexSuffix;

            $actions[] = new IndexAction($update, $index);
        }

        // If there were no updates generated we have nothing to update.
        if (\count($actions) === 0) {
            return;
        }

        $this->client->bulk($actions);
    }

    /**
     * Batches a search handler's iterable into batch sizes.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param int $batchSize
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[][]
     */
    private function getBatchedIterable(TransformableSearchHandlerInterface $handler, int $batchSize): iterable
    {
        $batch = [];

        foreach ($handler->getFillIterable() as $item) {
            $batch[] = $item;

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
