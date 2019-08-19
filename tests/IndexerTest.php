<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use EoneoPay\Utils\DateTime;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\AliasNotFoundException;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\EntityEntitySearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\EntitySearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\EntityManagerHelperStub;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub as ElasticClientStub;

/**
 * @covers \LoyaltyCorp\Search\Indexer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 */
class IndexerTest extends TestCase
{
    /**
     * Generate data to test non doctrine handler populates index.
     *
     * @return iterable
     */
    public function generatePopulateDataForNonDoctrineHandler(): iterable
    {
        $data = [
            'handler' => new NonDoctrineHandlerStub([
                'request-id' => ['key' => 'value']
            ]),
            'expected' => [
                'body' => [
                    [
                        'index' => [
                            '_index' => 'non-doctrine-index_new',
                            '_type' => 'doc',
                            '_id' => 'request-id'
                        ]
                    ],
                    ['key' => 'value']
                ]
            ]
        ];

        yield 'Handler with data' => $data;

        $data = [
            'handler' => new NonDoctrineHandlerStub([]),
            'expected' => null
        ];

        yield 'Handler with no data' => $data;
    }


    /**
     * Ensure the search handler index + '_new' index gets created
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testAliasGetsCreated(): void
    {
        $elasticClient = new ClientStub();
        $indexer = $this->createInstance($elasticClient);

        $expectedAlias = 'valid_new';
        $expectedIndexCreate = [
            'name' => 'valid_20190102030405',
            'mappings' => [
                'doc' => [
                    'properties' => [
                        'createdAt' => [
                            'type' => 'date'
                        ]
                    ]
                ]
            ],
            'settings' => [
                'number_of_replicas' => 1,
                'number_of_shards' => 1
            ]
        ];

        $now = new DateTime('2019-01-02T03:04:05');
        $indexer->create(new EntitySearchHandlerStub(), $now);

        self::assertSame([$expectedAlias], $elasticClient->getCreatedAliases());
        self::assertSame([$expectedIndexCreate], $elasticClient->getCreatedIndices());
    }

    /**
     * Ensure the cleaning process only disregards indices unrelated to search handlers
     *
     * @return void
     */
    public function testCleaningIndicesDoesNotRemoveUnrelatedIndices(): void
    {
        $client = new ClientStub(
            null,
            null,
            // unrelated-index and irrelevant-index should not be touched, because they are unrelated to search handlers
            [['name' => 'unrelated-index'], ['name' => 'irrelevant-index'], ['name' => 'valid-123']]
        );
        $indexer = $this->createInstance($client);
        $expected = ['valid-123'];

        $indexer->clean([new EntitySearchHandlerStub()]);

        self::assertSame($expected, $client->getDeletedIndices());
    }

    /**
     * Ensure the cleaning process only cares about indices that are related to search handlers
     *
     * @return void
     */
    public function testCleaningIndicesRepectsIndicesFromAliases(): void
    {
        $client = new ClientStub(
            null,
            null,
            [['name' => 'unrelated-index'], ['name' => 'valid-unused']],
            [['index' => 'valid', 'name' => 'anything']]
        );
        $indexer = $this->createInstance($client);
        $expected = ['valid-unused'];

        $indexer->clean([new EntitySearchHandlerStub()]);

        self::assertSame($expected, $client->getDeletedIndices());
    }

    /**
     * Ensure the cleaning process does not execute if dry run is true
     *
     * @return void
     */
    public function testCleaningIndicesRespectsDryOption(): void
    {
        $client = new ClientStub(
            null,
            null,
            [['name' => 'unrelated-index'], ['name' => 'irrelevant-index'], ['name' => 'valid-123']]
        );
        $indexer = $this->createInstance($client);

        $indexer->clean([new EntitySearchHandlerStub()], true);

        self::assertSame([], $client->getDeletedIndices());
    }

    /**
     * Ensure dry running the index swap method does not call anything from elastic client
     *
     * @return void
     */
    public function testIndexSwapperDryRun(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);
        $expected = ['valid_new'];

        $indexer->indexSwap([new EntitySearchHandlerStub()]);

        self::assertSame($expected, $elasticClient->getDeletedAliases());
    }

    /**
     * Ensure the swap method removes the _new alias
     *
     * @return void
     */
    public function testIndexSwapperRemovesNewAlias(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);

        $indexer->indexSwap([new EntitySearchHandlerStub()], true);

        self::assertSame([], $elasticClient->getSwappedAliases());
        self::assertSame([], $elasticClient->getDeletedAliases());
    }

    /**
     * Ensure the index<->alias swap does indeed happen
     *
     * @return void
     */
    public function testIndexSwapperSwapsAlias(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);
        // alias => index
        $expected = ['valid' => 'valid_201900502'];

        $indexer->indexSwap([new EntitySearchHandlerStub()]);

        self::assertSame($expected, $elasticClient->getSwappedAliases());
    }

    /**
     * Ensure the index swap method throws an Exception if no *_new alias can be found
     *
     * @return void
     */
    public function testIndexSwapperThrowsExceptionIfAliasNotFound(): void
    {
        $this->expectException(AliasNotFoundException::class);
        $this->expectExceptionMessage('Could not find expected alias \'valid_new\'');

        $elasticClient = new ClientStub(true);
        $indexer = $this->createInstance($elasticClient);

        $indexer->indexSwap([new EntitySearchHandlerStub()]);
    }

    /**
     * Index population happens in batches, loops are involved, and then whatever is left over unpopulated, outside
     * of these loops should be still handled
     *
     * @return void
     */
    public function testLeftoverIterationsGetUpdated(): void
    {
        $manager = new ManagerStub();

        // 6 documents, that way there is one loop of batched 5, and one left over unhandled
        $entityManagerHelper = new EntityManagerHelperStub(6);
        $indexer = $this->createInstance(null, $entityManagerHelper, $manager);

        $indexer->populate(new EntitySearchHandlerStub(), '', 5);

        // 2 calls to handleUpdate should be done, one within the batch loop, and one for the left over data
        self::assertSame(2, $manager->getUpdateCount());
    }

    /**
     * Ensure objects are being passed through to the manager
     *
     * @return void
     */
    public function testPopulatePassesObjectsToManager(): void
    {
        $manager = new ManagerStub();
        $entityManagerHelper = new EntityManagerHelperStub(2);
        $indexer = $this->createInstance(null, $entityManagerHelper, $manager);

        $expected = [
            [
                'class' => EntityStub::class,
                'indexSuffix' => '_new',
                'objects' => [
                    new EntityStub(),
                    new EntityStub()
                ]
            ]
        ];

        /**
         * Despite these SearchableStub objects not being passed into the populate command
         * the manager should have received it from the indexer as objects
         */

        $indexer->populate(new EntityEntitySearchHandlerStub(), '_new', 2);

        self::assertEquals($expected, $manager->getUpdateObjects());
    }

    /**
     * Test populating a non doctrine handler into search.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $handler
     * @param mixed[]|null $expected
     *
     * @return void
     *
     * @dataProvider generatePopulateDataForNonDoctrineHandler
     */
    public function testPopulatingNonDoctrineHandler(SearchHandlerInterface $handler, ?array $expected = null): void
    {
        $elasticClient = new ElasticClientStub();
        $client = new Client($elasticClient);
        $indexer = $this->createInstance($client);

        $indexer->populate($handler, '_new');

        self::assertSame($expected, $elasticClient->getBulkParameters());
    }

    /**
     * Ensure the search handler index + '_new' alias is deleted so it can be re-created, when it pre-exists
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testTemporaryAliasDeleted(): void
    {
        $elasticClient = new ClientStub(true);
        $indexer = $this->createInstance($elasticClient);
        $expected = ['valid_new'];

        $indexer->create(new EntitySearchHandlerStub());

        // No deleted aliases because *_new was not existing already
        self::assertSame($expected, $elasticClient->getDeletedAliases());
    }

    /**
     * Instantiate an Indexer
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface|null $client
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface|null $entityManagerHelper
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface|null $manager
     *
     * @return \LoyaltyCorp\Search\Indexer
     */
    private function createInstance(
        ?ClientInterface $client = null,
        ?EntityManagerHelperInterface $entityManagerHelper = null,
        ?ManagerInterface $manager = null
    ): Indexer {
        return new Indexer(
            $client ?? new ClientStub(),
            $entityManagerHelper ?? new EntityManagerHelperStub(),
            $manager ?? new ManagerStub()
        );
    }
}
