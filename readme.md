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

use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

/**
 * This handler represents a single index in Elasticsearch, and reacts to a single primary
 * entity for building those indicies.
 * 
 * The extends annotation below tells phpstan that we're creating a handler for dealing
 * with Transactions only, and enables additional checks to ensure code correctness. For
 * more details, check out PhpStan Generics.
 * 
 * @extends DoctrineSearchHandler<Transaction>
 */
class TransactionHandler extends DoctrineSearchHandler 
{
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
                    // Additional mappings as required
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
    public function getSubscriptions(): iterable
    {
        yield from parent::getSubscriptions();
        
        // React to transaction metadata changes.
        yield new ChangeSubscription(
            Metadata::class,
            ['key', 'value'],
            fn (ChangedEntity $change) => $this->loadTransactionsFromMetadata($change)
        );

        // React to changes to the user's email address
        yield new ChangeSubscription(
            User::class,
            ['email'],
            fn (ChangedEntity $change) => $this->loadTransactionsFromUser($change)      
         );
    }

    /**
     * Loads related transactions from a metadata change.
     *
     * @phpstan-return iterable<ObjectForUpdate<Transaction>>
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $change
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     */
    public function loadTransactionsFromMetadata(ChangedEntity $change): iterable
    {
        if ($change->getClass() !== Metadata::class ||
            \is_string($change->getIds()['metadataId'] ?? null) === false) {
            return [];
        }

        $repository = $this->getEntityManager()->getRepository(Transaction::class);

        return $repository->getSearchTransactionsForMetadataUpdate($change->getIds()['metadataId']);
    }

    /**
     * Loads related transactions from a user.
     *
     * @phpstan-return iterable<ObjectForUpdate<Transaction>>
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $change
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     */
    public function loadTransactionsFromUser(ChangedEntity $change): iterable
    {
        if ($change->getClass() !== User::class ||
            \is_string($change->getIds()['userId'] ?? null) === false) {
            return [];
        }

        $repository = $this->getEntityManager()->getRepository(Transaction::class);

        return $repository->getSearchTransactionsForUserUpdate($change->getIds()['userId']);
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
        // We didnt get a $change that makes sense for this transform method.
        if ($change->getClass() !== Transaction::class ||
            ($change->getObject() instanceof Transaction) === false) {
            return null;
        }
        
        // If we got an ObjectForDelete and we have the searchId metadata,
        // issue a delete action to search.
        if ($change instanceof ObjectForDelete === true &&
            \is_string($change->getMetadata()['searchId'] ?? null) === true) {
            return new DocumentDelete($change->getMetadata()['searchId']);
        }

        // If we didnt get an Update or Delete we dont know what the system// 
        // wants, lets not do anything.
        if ($change instanceof ObjectForUpdate === false) {
            return null;
        }

        /**
         * @var \App\Database\Entities\Transaction $transaction
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === check
         */
        $transaction = $change->getObject();

        // An object without an external id cannot be transformed.
        if (\is_string($transaction->getExternalId()) === false) {
            return null;
        }

        return new DocumentUpdate(
            $transaction->getExternalId(),
            [
                'id' => $transaction->getId(),
                'created_at' => $transaction->getCreatedAt(),
                // ...
            ]
        );
    }
}
```

### Example Entity Repository

Along with the search handler, there are a few methods that need to be implemented into
the entity's repository. The package provides a SearchRepository trait that does the
heavy lifting, but you still need to implement the interface and a few methods that
proxy to the trait.

```php
<?php
declare(strict_types=1);

namespace App\Database\Repositories;

use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;
use LoyaltyCorp\Search\Bridge\Doctrine\SearchRepositoryTrait;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

/**
 * @implements FillableRepositoryInterface<Transaction>
 */
class TransactionRepository extends Repository implements FillableRepositoryInterface
{
    use SearchRepositoryTrait;

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function getFillIterable(): iterable
    {
        return $this->doGetFillIterable(
            $this->createQueryBuilder('e'),
            $this->entityManager->getClassMetadata(Transaction::class),
            Transaction::class
        );
    }

    /**
     * Returns an iterable of transactions that relate to a user.
     *
     * @phpstan-return array<ObjectForUpdate<Transaction>>
     *
     * @param string $metadataId
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate[]
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function getSearchTransactionsForMetadataUpdate(string $metadataId): iterable
    {
        $builder = $this->createQueryBuilder('t');
        $builder->select('t.transactionId');

        $builder->where(':metadata MEMBER OF t.metadata');
        $builder->setParameter('metadata', $metadataId);

        $index = 0;
        foreach ($builder->getQuery()->iterate([], AbstractQuery::HYDRATE_SCALAR) as $result) {
            yield new ObjectForUpdate(
                Transaction::class,
                ['transactionId' => $result[$index++]['transactionId']]
            );
        }
    }

    /**
     * Returns an iterable of transactions that relate to a user.
     *
     * @phpstan-return array<ObjectForUpdate<Transaction>>
     *
     * @param string $userId
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate[]
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function getSearchTransactionsForUserUpdate(string $userId): iterable
    {
        $builder = $this->createQueryBuilder('t');
        $builder->select('t.transactionId');

        $builder->where('IDENTITY(t.user) = :user');
        $builder->setParameter('user', $userId);

        $index = 0;
        foreach ($builder->getQuery()->iterate([], AbstractQuery::HYDRATE_SCALAR) as $result) {
            yield new ObjectForUpdate(
                Transaction::class,
                ['transactionId' => $result[$index++]['transactionId']]
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function prefillSearch(iterable $changes): void
    {
        $this->doPrefillSearch(
            $this->createQueryBuilder('e'),
            $this->entityManager->getClassMetadata(Transaction::class),
            Transaction::class,
            $changes
        );
    }
}
```
