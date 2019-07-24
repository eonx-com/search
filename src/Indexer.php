<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use EoneoPay\Utils\DateTime;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;

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
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $manager;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $elasticClient
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface $entityManagerHelper
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $manager
     */
    public function __construct(
        ClientInterface $elasticClient,
        EntityManagerHelperInterface $entityManagerHelper,
        ManagerInterface $manager
    ) {
        $this->elasticClient = $elasticClient;
        $this->entityManagerHelper = $entityManagerHelper;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function create(HandlerInterface $searchHandler): void
    {
        $index = $searchHandler->getIndexName();

        $dateStamp = (new DateTime())->format('Ymdhis');

        // Format new index name based on root search handler index name, and the current date
        $newIndex = \sprintf('%s_%s', $index, $dateStamp);

        // Alias to correlate the index with the 'latest' one (re)created
        $tempAlias = \sprintf('%s_new', $index);

        $this->elasticClient->createIndex($newIndex);

        // Remove _new alias if already exists index, before we re-use the temporary alias name
        if ($this->elasticClient->isAlias($tempAlias) === true) {
            $this->elasticClient->deleteAlias('*', $tempAlias);
        }

        $this->elasticClient->createAlias($index, $tempAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function populate(HandlerInterface $searchHandler, ?int $batchSize = null): void
    {
        $documents = [];
        $iteration = 0;

        // Iterate over all primary keys of the dedicated entity against the search handler
        foreach ($this->entityManagerHelper->iterateAllIds(
            $searchHandler->getHandledClass()
        ) as $identifier) {
            $documents[] = $identifier;

            // Create documents in batches to avoid overloading memory & request size
            if ($iteration > 0 && $iteration % ($batchSize ?? 100) === 0) {
                $this->manager->handleUpdates($searchHandler->getHandledClass(), $documents);
                $documents = [];
            }

            $iteration++;
        }

        // Handle creation of remaining documents that were not batched because the loop finished
        if (\count($documents) > 0) {
            $this->manager->handleUpdates(
                $searchHandler->getHandledClass(),
                $documents
            );
        }
    }
}
