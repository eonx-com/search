<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\CustomAccessHandlerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;

final class Populator implements PopulatorInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface
     */
    private $accessPopulator;

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
     * @param \LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface $accessPopulator
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface $nameTransformer
     */
    public function __construct(
        AccessPopulatorInterface $accessPopulator,
        ClientInterface $client,
        IndexNameTransformerInterface $nameTransformer
    ) {
        $this->accessPopulator = $accessPopulator;
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
            $searchId = $handler->getSearchId($object);
            // If the handler didnt return an identifier, there is nothing to index.
            if ($searchId === null) {
                continue;
            }

            $index = $this->nameTransformer->transformIndexName($handler, $object) . $indexSuffix;

            $transformed = $handler->transform($object);
            // If the handler returned null, the document should be deleted (if it exists).
            if ($transformed === null) {
                $actions[] = new IndexAction(new DocumentDelete($searchId), $index);

                continue;
            }

            // If the handler is not doing its own security, add access tokens to the document.
            if ($handler instanceof CustomAccessHandlerInterface === false) {
                $accessTokens = $this->accessPopulator->getAccessTokens($object);

                $transformed[AccessTokenMappingHelper::ACCESS_TOKEN_PROPERTY] = $accessTokens;
            }

            $actions[] = new IndexAction(
                new DocumentUpdate((string) $searchId, $transformed),
                $index
            );
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
     * @return mixed[]
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
