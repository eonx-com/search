# Search

The goals of this search package are to provide common functionality that enables creating and
maintaining Elasticsearch search indices for an application.

It is up to the application to provide implementations of the `SearchHandlerInterface` (for simple
search indices that the package will ensure are created) and `TransformableSearchHandlerInterface`
(for indices that will react to changes to entities in external systems and transform those entities
into search documents to be indexed).

An example implementation of both is provided.

## Overview

This package provides multiple parts to enable easy search.

- There are index management commands provided that allow for reindexing without destroying the 
  previous indices.
- Command options that will only reindex when the index mappings have changed 
  (not yet implemented PYMT-1690)
- The Lumen service provider will auto discover any services tagged with `search_handler` and will
  use those discovered handlers to create and manage indices in Elasticsearch.
- The Lumen bridge providers a listener that will react to any Doctrine entity changes through the
  use of EasyEntityChange.
- The search handler interfaces provide multiple options for implementation depending on the 
  application requirements.
  
## Theory of Operation

The primary and default implementation of this package sets up a listener that will react to any
entity changes inside Doctrine and dispatch jobs for re-indexing those entities based on Search
Handlers that are interested in specific changes.

Each application Search Handler will define an array of `ChangeSubscription` DTOs that describe the 
entities and relevant properties that should trigger the reindexing of a document.

The package will handle batching search updates into multiple jobs for handling, and pass a 
`ObjectForChange` DTO to the application Search Handler that describes an object that has changed -
either the object was updated or deleted. It is then up to the Search Handler to return a 
`DocumentAction` DTO that describes what should happen to the Elasticsearch document.

### Lifecycle - Index management

When an application is initially created or deployed, the indices **must** be created before the
application writes to Elasticsearch. Elasticsearch will eagerly create indices which is a behavior
we dont want- so before the application accepts requests a migration/search setup process must run.

Following an imaginary index `transactions` through the following process:

```bash
# This command will create initial indices that are suffixed with the current date, and add an alias
# for each one that is suffixed with _new. No aliases exist at the root at this time.
#
# The system creates a `transactions_20200102121314` and a `transactions_new` alias that points to
# the date suffixed index.
$ ./artisan search:index:create

# This command fills the _new aliases with all document data for any search handlers that implement
# the TransformableSearchHandlerInterface. This command has options for synchronously filling or
# creating jobs to fill with workers.
#
# The system fills all data from the `getFillIterable` method on the TransactionSearchHandler. The
# index is still not live at this point.
$ ./artisan search:index:fill

# This command will atomically swap any root (live) aliases for any indices suffixed with _new that
# have had data populated. After this command is run, the application has been switched to the new
# indexes.
#
# The system sees that `transactions_new` (which points to `transactions_20200102121314`) has data 
# in it, and atomically swaps `transactions` (which currently points to 
# `transactions_20191212121212`) to now point to `transactions_20200102121314`. All index changes 
# occur at the same time and if any fail to swap, they all fail.
$ ./artisan search:index:live

# This command cleans up any old aliases/indices that are no longer required.
#
# The `transactions_2019121212` index is removed.
$ ./artisan search:index:clean
```

### Lifecycle - reacting to Doctrine changes

This package listens for `EntityChange` events from the EasyEntityChange package. The 
`EntityUpdateWorker` converts these events into `ObjectForChange` DTOs that are then processed
against the Search Handler subscriptions to find any intersections.

Once any intersections are found, the work is batched and dispatched as jobs for workers to process
as required.

## Example Search Handler

The below example is verbose, and contains code that would normally be placed inside an abstract
search handler, to show the expectations of an implementation of the 
`TransformableSearchHandlerInterface`.

```php
<?php
declare(strict_types=1);

namespace App\Services\Search;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

class TransactionHandler implements TransformableSearchHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This method is used to define the Elasticsearch mappings. By convention, our indices should
     * be defined with dynamic->strict wherever they can be, to avoid issues with mistakes in the
     * transform method or the mappings being out of sync.
     *
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [
            'doc' => [
                'dynamic' => 'strict',
                'properties' => [
                    'createdAt' => [
                        'type' => 'date',
                    ],
                ],
            ],
        ];
    }

    /**
     * Depending on the requirements of the application and if the Elasticsearch system is clustered
     * these settings may need to be modified, but for a default implementation with a single ES node
     * the defaults of 1 for both settings are preferred.
     *
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [
            'number_of_replicas' => 1,
            'number_of_shards' => 1,
        ];
    }

    /**
     * This method is used during re-indexing to gather an iterable that should return every single
     * entity that needs to be indexed when reindexing. The returned results should be
     * ObjectForUpdate DTOs which will then be used to batch the reindexing into multiple jobs.
     *
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select('t.transactionId');
        $builder->from(Transaction::class, 't');

        $builder->where('t.status != :void');
        $builder->setParameter('void', Transaction::VOIDED);

        // We order the transactions by date so that newer transactions are indexed first, which in
        // an emergency if we had to reindex live more relevant data is indexed at the start.
        $builder->addOrderBy('t.created_at', 'DESC');

        foreach ($builder->getQuery()->iterate(null, Query::HYDRATE_SCALAR) as $result) {
            yield new ObjectForUpdate(Transaction::class, $result['transactionId']);
        }
    }

    /**
     * The handler key is used internally by the search package to keep track of which handler needs
     * what data, the key needs to be unique across the application.
     *
     * {@inheritdoc}
     */
    public function getHandlerKey(): string
    {
        return 'transactions';
    }

    /**
     * This is the index name that will be used by the search package. Indexes will be created with
     * a date suffix and aliased to the "real index" name during the search setup or reindexing
     * process.
     *
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'transactions';
    }

    /**
     * This method returns the subscriptions for any objects that this search handler is interested
     * in - and optionally a transformation callback that will turn the ChangedEntity that it
     * receives into an iterable of ObjectForUpdate DTOs for batch processing.
     *
     * {@inheritdoc}
     */
    public function getSubscriptions(): array
    {
        return [
            // React to any transaction changes.
            new ChangeSubscription(
                Transaction::class,
                ['created_at', 'amount', 'status', /* all properties that are used by transform() */]
            ),

            // React to any customer name changes.
            new ChangeSubscription(
                Customer::class,
                ['name'],
                static function (ChangedEntity $change): iterable {
                    // If we've deleted the customer the transactions shouldnt be deleted so
                    // we return no changes.
                    if ($change instanceof DeletedEntity === true) {
                        return [];
                    }

                    $builder = $this->entityManager->createQueryBuilder();

                    $builder->select('t.transactionId');
                    $builder->from(Transaction::class, 't');

                    $builder->where('t.status != :void');
                    $builder->setParameter('void', Transaction::VOIDED);

                    // Since the customer's name has changed and we index the customer's name as
                    // part of the transaction index we need to gather all transactions for the
                    // customer.
                    $builder->andWhere('IDENTITY(t.customer) = :customer');
                    $builder->setParameter('customer', $change->getIds()['customerId'] ?? null);

                    // We order the result by date so that newer transactions are indexed first, which in
                    // an emergency if we had to reindex live more relevant data is indexed at the start.
                    $builder->addOrderBy('t.created_at', 'DESC');

                    foreach ($builder->getQuery()->iterate(null, Query::HYDRATE_SCALAR) as $result) {
                        yield new ObjectForUpdate(Transaction::class, $result['transactionId']);
                    }
                }
            )
        ];
    }

    /**
     * This function will prefill the ObjectForChange with actual entities, which is done in a batch
     * instead of singularly looking up entities.
     *
     * {@inheritdoc}
     */
    public function prefill(iterable $changes): void
    {
        // This is a contrived example, but an example implementation for this method
        // can be seen in the abstract DoctrineSearchHandler and SearchRepository
        // classes of this library.
        foreach ($changes as $change) {
            $change->setObject($this->lookupObject($change));
        }
    }

    /**
     * This method takes an ObjectForChange and returns a DocumentAction.
     *
     * Its primary purpose is to either decide that a document should be deleted or updated.
     *
     * {@inheritdoc}
     */
    public function transform(ObjectForChange $change): ?DocumentAction
    {
        if ($change instanceof ObjectForDelete === true) {
            return new DocumentDelete($change->getMetadata()['searchId'] ?? '');
        }

        // If we didnt get an Update or Delete we dont know what the system wants, lets not do
        // anything.
        if ($change instanceof ObjectForUpdate === false) {
            return null;
        }

        $transaction = $change->getObject() ?? $this->lookupObject($change);

        return new DocumentUpdate(
            $change->getMetadata()['searchId'] ?? '',
            [
                'id' => $transaction->getId(),
                'created_at' => $transaction->getCreatedAt(),
                // ...
            ]
        );
    }

    /**
     * Looks up the object in the database, otherwise throwing when the
     * object couldnt be found.
     *
     * @return Transaction
     */
    private function lookupObject(ObjectForChange $object): Transaction
    {
        $transaction = $this->entityManager->getRepository(Transaction::class)
            ->findOneBy($object->getIds());

        if ($transaction instanceof Transaction === false) {
            throw new InvalidChange();
        }

        return $transaction;
    }
}
```
