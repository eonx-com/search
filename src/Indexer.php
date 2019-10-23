<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use DateTime as BaseDateTime;
use EoneoPay\Utils\DateTime;
use LoyaltyCorp\Search\Exceptions\AliasNotFoundException;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for search indexer already using decoupled services.
 */
final class Indexer implements IndexerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $elasticClient;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface
     */
    private $indexTransformer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $manager;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\PopulatorInterface
     */
    private $populator;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $elasticClient
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface $indexTransformer
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $manager
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface $populator
     */
    public function __construct(
        ClientInterface $elasticClient,
        IndexNameTransformerInterface $indexTransformer,
        ManagerInterface $manager,
        PopulatorInterface $populator
    ) {
        $this->elasticClient = $elasticClient;
        $this->indexTransformer = $indexTransformer;
        $this->manager = $manager;
        $this->populator = $populator;
    }

    /**
     * {@inheritdoc}
     */
    public function clean(array $searchHandlers, ?bool $dryRun = null): IndexCleanResult
    {
        $indicesUsedByAlias = [];
        $allIndices = [];
        $handlerIndices = [];

        /** @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $searchHandlers */
        foreach ($searchHandlers as $searchHandler) {
            $handlerIndices[] = $this->indexTransformer->transformIndexNames($searchHandler);
        }

        // Build array of all indices used by aliases
        foreach ($this->elasticClient->getAliases() as $alias) {
            $indicesUsedByAlias[] = $alias['index'];
        }

        // Flatten a multi dimensional array of index names into a single dimension
        $knownIndices = \array_merge(...$handlerIndices);

        // Build array of all indices
        foreach ($this->elasticClient->getIndices() as $index) {
            // Disregard any indices that are not to do with search handlers
            if ($this->indexStartsWith($index['name'], $knownIndices) === false) {
                continue;
            }

            $allIndices[] = $index['name'];
        }

        // Remove double-ups of indices being used from aliases
        $indicesUsedByAlias = \array_unique($indicesUsedByAlias);

        // Determine which indices are not used by an alias
        $unusedIndices = \array_diff($allIndices, $indicesUsedByAlias);

        $results = new IndexCleanResult($unusedIndices);

        if (($dryRun ?? false) === true) {
            return $results;
        }

        // Remove any indices unused by a root alias
        foreach ($unusedIndices as $unusedIndex) {
            $this->elasticClient->deleteIndex($unusedIndex);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function create(SearchHandlerInterface $searchHandler, ?BaseDateTime $now = null): void
    {
        $indexNames = $this->indexTransformer->transformIndexNames($searchHandler);

        $now = $now ?? new DateTime();
        $dateStamp = $now->format('Ymdhis');

        foreach ($indexNames as $indexName) {
            // Format new index name based on root search handler index name, and the current date
            $newIndex = \sprintf('%s_%s', $indexName, $dateStamp);

            // Alias to correlate the index with the 'latest' one (re)created
            $tempAlias = \sprintf('%s_new', $indexName);

            $this->elasticClient->createIndex(
                $newIndex,
                $searchHandler::getMappings(),
                $searchHandler::getSettings()
            );

            // Remove _new alias if already exists index, before we re-use the temporary alias name
            if ($this->elasticClient->isAlias($tempAlias) === true) {
                $this->elasticClient->deleteAlias([$tempAlias]);
            }

            $this->elasticClient->createAlias($newIndex, $tempAlias);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\AliasNotFoundException
     */
    public function indexSwap(array $searchHandlers, ?bool $dryRun = null): IndexSwapResult
    {
        $aliasesToMove = [];
        $aliasedToRemove = [];
        $indexToSkip = [];

        foreach ($searchHandlers as $searchHandler) {
            $indexNames = $this->indexTransformer->transformIndexNames($searchHandler);

            foreach ($indexNames as $indexName) {
                // Use index+_new to determine the latest index name
                $newAlias = \sprintf('%s_new', $indexName);

                /** @var string[]|null $latestIndex */
                $latestAlias = $this->elasticClient->getAliases($newAlias)[0] ?? null;

                if ($latestAlias === null) {
                    throw new AliasNotFoundException(\sprintf('Could not find expected alias \'%s\'', $newAlias));
                }

                if ($this->elasticClient->isAlias($indexName) === true &&
                    $this->elasticClient->count($newAlias) === 0 &&
                    $this->elasticClient->count($indexName) > 0) {
                    $indexToSkip[] = $latestAlias['index'];
                    $aliasedToRemove[] = $newAlias;
                    /**
                     * Swapping should not occur if all the conditions are met:
                     *     - The root alias exists
                     *     - New index has no documents
                     *     - Old index contains data
                     * Generally this is true when the SearchIndex type is not entity based
                     */

                    continue;
                }

                $aliasesToMove[] = ['alias' => $indexName, 'index' => $latestAlias['index']];
                $aliasedToRemove[] = $newAlias;
            }
        }

        $actions = new IndexSwapResult($aliasesToMove, $aliasedToRemove, $indexToSkip);

        if (($dryRun ?? false) === true) {
            return $actions;
        }

        // Atomically switch which index the root alias is associated with
        $this->elasticClient->moveAlias($aliasesToMove);

        // Remove *_new alias for this handler
        $this->elasticClient->deleteAlias($aliasedToRemove);

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(TransformableSearchHandlerInterface $handler, string $indexSuffix, int $batchSize): void
    {
        $iterable = $this->populator->getBatchedIterable($handler, $batchSize);

        foreach ($iterable as $batch) {
            $this->manager->handleUpdatesWithHandler($handler, $indexSuffix, $batch);
        }
    }

    /**
     * Determine if provided index name starts with any of the specified values
     *
     * @param string $index
     * @param string[] $indexPrefixes
     *
     * @return bool True indicates the index start start with a value within the supplied prefixes
     */
    private function indexStartsWith(string $index, array $indexPrefixes): bool
    {
        foreach ($indexPrefixes as $indexPrefix) {
            if (\strpos($index, $indexPrefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
