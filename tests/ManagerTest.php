<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use Elasticsearch\Client as BaseClient;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Manager;
use LoyaltyCorp\Search\Transformers\ObjectTransformer;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NotSearchableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\ProviderAwareSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoDocumentBodyStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoSearchIdStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

/**
 * @covers \LoyaltyCorp\Search\Manager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class ManagerTest extends TestCase
{
    /**
     * Test getSearchMeta() functionality
     *
     * @return void
     */
    public function testGetSearchMetaFunctionality(): void
    {
        $handlers = new RegisteredSearchHandlerStub([new TransformableSearchHandlerStub()]);
        $manager = $this->getManager($handlers);

        // Test against a searchable object
        self::assertSame(['valid' => 'searchable'], $manager->getSearchMeta(new SearchableStub()));

        // Test against a non-searchable object
        self::assertSame([], $manager->getSearchMeta(new NotSearchableStub()));

        // Test against a searchable object which doesn't have an id
        self::assertSame([], $manager->getSearchMeta(new NoSearchIdStub()));
    }

    /**
     * Test handleDeletes() functionality
     *
     * @return void
     */
    public function testHandleDeletesFunctionality(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new TransformableSearchHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Test method passes through to elasticsearch
        $manager->handleDeletes(['index' => [['9']]]);
        self::assertSame(
            ['body' => [['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => ['9']]]]],
            $stub->getBulkParameters()
        );
    }

    /**
     * Test handleUpdates() functionality with provider aware search handler.
     *
     * @return void
     */
    public function testHandleProviderAwareUpdatesFunctionality(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new ProviderAwareSearchHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Test an unsupported class doesn't do anything
        $manager->handleUpdates(NotSearchableStub::class, '_new', []);
        self::assertNull($stub->getBulkParameters());

        // Test supported class only generates body for valid classes
        $manager->handleUpdates(SearchableStub::class, '_new', [
            new NoDocumentBodyStub(),
            new NoSearchIdStub(),
            new SearchableStub()
        ]);

        self::assertSame(
            [
                'body' => [
                    [
                        'index' => [
                            '_index' => 'provider-aware-index_customId_new',
                            '_type' => 'doc',
                            '_id' => 'searchable'
                        ]
                    ],
                    ['search' => 'body']
                ]
            ],
            $stub->getBulkParameters()
        );
    }

    /**
     * Test handleUpdates() functionality
     *
     * @return void
     */
    public function testHandleUpdatesFunctionality(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new TransformableSearchHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Test an unsupported class doesn't do anything
        $manager->handleUpdates(NotSearchableStub::class, '_new', []);
        self::assertNull($stub->getBulkParameters());

        // Test supported class only generates body for valid classes
        $manager->handleUpdates(SearchableStub::class, '_new', [
            new NoDocumentBodyStub(),
            new NoSearchIdStub(),
            new SearchableStub()
        ]);

        self::assertSame(
            [
                'body' => [
                    [
                        'index' => [
                            '_index' => 'valid_new',
                            '_type' => 'doc',
                            '_id' => 'searchable'
                        ]
                    ],
                    ['search' => 'body']
                ]
            ],
            $stub->getBulkParameters()
        );
    }

    /**
     * Test handleUpdates() functionality when no transformations occur
     *
     * @return void
     */
    public function testHandleUpdatesWhenNoTransformationsOccur(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new TransformableSearchHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Tests whats going to happen when handleUpdates is called with objects that result
        // in no transformations
        $manager->handleUpdates(SearchableStub::class, '', [
            new NoDocumentBodyStub()
        ]);

        self::assertNull($stub->getBulkParameters());
    }

    /**
     * Ensure no results are returned when a handler is passed an object that has no searchId
     *
     * @return void
     */
    public function testSearchMetaReturnsNothingWhenSearchIdNulled(): void
    {
        $handlers = new RegisteredSearchHandlerStub([new NotSearchableSearchHandlerStub()]);
        $manager = $this->getManager($handlers);

        $result = $manager->getSearchMeta(new NotSearchableStub());

        self::assertSame([], $result);
    }

    /**
     * Instantiate an ElasticSearch client
     *
     * @param \Elasticsearch\Client|null $client
     *
     * @return \LoyaltyCorp\Search\Client
     */
    private function createClient(?BaseClient $client = null): Client
    {
        return new Client(
            $client ?? new ClientStub(),
            new ClientBulkResponseHelper()
        );
    }

    /**
     * Gets the manager under test.
     *
     * @param \Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub $handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface|null $client
     *
     * @return \LoyaltyCorp\Search\Manager
     */
    private function getManager(
        RegisteredSearchHandlerStub $handlers,
        ?ClientInterface $client = null
    ): Manager {
        return new Manager(
            $handlers,
            $client ?? $this->createClient(),
            new DefaultIndexNameTransformer(),
            new ObjectTransformer(),
        );
    }
}
