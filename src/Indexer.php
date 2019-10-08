<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use DateTime as BaseDateTime;
use EoneoPay\Utils\DateTime;
use LoyaltyCorp\Search\Exceptions\AliasNotFoundException;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\IndexHelperInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

final class Indexer implements IndexerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $elasticClient;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface
     */
    private $entityManagerHelper;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\IndexHelperInterface
     */
    private $indexHelper;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $manager;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $elasticClient
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface $entityManagerHelper
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\IndexHelperInterface $indexHelper
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $manager
     */
    public function __construct(
        ClientInterface $elasticClient,
        EntityManagerHelperInterface $entityManagerHelper,
        IndexHelperInterface $indexHelper,
        ManagerInterface $manager
    ) {
        $this->elasticClient = $elasticClient;
        $this->entityManagerHelper = $entityManagerHelper;
        $this->indexHelper = $indexHelper;
        $this->manager = $manager;
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
            $handlerIndices[] = $this->indexHelper->getIndexName($searchHandler);
        }

        // Build array of all indices used by aliases
        foreach ($this->elasticClient->getAliases() as $alias) {
            $indicesUsedByAlias[] = $alias['index'];
        }

        // Build array of all indices
        foreach ($this->elasticClient->getIndices() as $index) {
            // Disregard any indices that are not to do with search handlers
            if ($this->indexStartsWith($index['name'], $handlerIndices) === false) {
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
        $index = $this->indexHelper->getIndexName($searchHandler);

        $now = $now ?? new DateTime();
        $dateStamp = $now->format('Ymdhis');

        // Format new index name based on root search handler index name, and the current date
        $newIndex = \sprintf('%s_%s', $index, $dateStamp);

        // Alias to correlate the index with the 'latest' one (re)created
        $tempAlias = \sprintf('%s_new', $index);

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
            $indexName = $this->indexHelper->getIndexName($searchHandler);
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
    public function populate(
        EntitySearchHandlerInterface $searchHandler,
        string $indexSuffix,
        ?int $batchSize = null
    ): void {
        // Populate index of search handler on a per-entity basis
        foreach ($searchHandler->getHandledClasses() as $handlerClass) {
            $this->populateIndex(
                $handlerClass,
                $indexSuffix,
                $batchSize
            );
        }
    }

    /**
     * Handle document updates from an array of entity identifiers
     *
     * @param string $class
     * @param string $indexSuffix
     * @param string[]|int[] $ids Array of primary keys for the given entity $class
     *
     * @return void
     */
    private function handleUpdatesFromPrimaryKeys(string $class, string $indexSuffix, array $ids): void
    {
        $entities = $this->entityManagerHelper->findAllIds($class, $ids);

        $this->manager->handleUpdates($class, $indexSuffix, $entities);
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

    /**
     * Populate an index with all documents
     *
     * @param string $class
     * @param string $indexSuffix
     * @param int|null $batchSize
     *
     * @return void
     */
    private function populateIndex(string $class, string $indexSuffix, ?int $batchSize = null): void
    {
        $documents = [];
        $iteration = 0;

        // Iterate over all primary keys of the dedicated entity against the search handler
        foreach ($this->entityManagerHelper->iterateAllIds($class) as $identifier) {
            $documents[] = $identifier;

            // Create documents in batches to avoid overloading memory & request size
            if ($iteration > 0 && $iteration % ($batchSize ?? 100) === 0) {
                $this->handleUpdatesFromPrimaryKeys($class, $indexSuffix, $documents);
                $documents = [];
            }

            $iteration++;
        }

        // Handle creation of remaining documents that were not batched because the loop finished
        if (\count($documents) > 0) {
            $this->handleUpdatesFromPrimaryKeys($class, $indexSuffix, $documents);
        }
    }

    /**
     * Create index name based on provider id if provided.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     * @param string|null $providerId
     *
     * @return string
     */
    private function createIndexName(SearchHandlerInterface $searchHandler, ?string $providerId): string
    {
        if (\is_string($providerId) !== true) {
            return $searchHandler->getIndexName();
        }

        return \sprintf('%s_%s', $searchHandler->getIndexName(), $providerId);
    }
}
