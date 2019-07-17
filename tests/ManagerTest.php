<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Manager;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoDocumentBodyStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoSearchIdStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

/**
 * @covers \LoyaltyCorp\Search\Manager
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
        $manager = new Manager([new HandlerStub()], new Client(new ClientStub()));

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
        $manager = new Manager([new HandlerStub()], new Client($stub));

        // Test method passes through to elasticsearch
        $manager->handleDeletes(['index' => [['9']]]);
        self::assertSame(
            ['body' => [['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => ['9']]]]],
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
        $manager = new Manager([new HandlerStub()], new Client($stub));

        // Test an unsupported class doesn't do anything
        $manager->handleUpdates(NotSearchableStub::class, []);
        self::assertNull($stub->getBulkParameters());

        // Test supported class only generates body for valid classes
        $manager->handleUpdates(SearchableStub::class, [
            new NoDocumentBodyStub(),
            new NoSearchIdStub(),
            new SearchableStub()
        ]);

        self::assertSame(
            ['body' =>
                [['index' => ['_index' => 'valid', '_type' => 'doc', '_id' => 'searchable']], ['search' => 'body']]
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
        $manager = new Manager([new HandlerStub()], new Client($stub));

        // Tests whats going to happen when handleUpdates is called with objects that result
        // in no transformations
        $manager->handleUpdates(SearchableStub::class, [
            new NoDocumentBodyStub()
        ]);

        self::assertNull($stub->getBulkParameters());
    }

    /**
     * Test isSearchable() asks the handler whether it's supported or not
     *
     * @return void
     */
    public function testIsSearchableAsksHandler(): void
    {
        $manager = new Manager([new HandlerStub()], new Client(new ClientStub()));

        self::assertTrue($manager->isSearchable(SearchableStub::class));
        self::assertFalse($manager->isSearchable(NotSearchableStub::class));
    }
}
