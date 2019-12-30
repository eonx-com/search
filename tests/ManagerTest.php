<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use Elasticsearch\Client as BaseClient;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Manager;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\PopulatorStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

/**
 * @covers \LoyaltyCorp\Search\Manager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class ManagerTest extends TestCase
{
    /**
     * Test handleDeletes() functionality.
     *
     * @return void
     */
    public function testHandleDeletesFunctionality(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new TransformableHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Test method passes through to elasticsearch
        $manager->handleDeletes(['index' => ['9', '10']]);

        $expectedRequest = [
            'body' => [
                ['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => '9']],
                ['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => '10']],
            ],
        ];

        self::assertSame(
            $expectedRequest,
            $stub->getBulkParameters()
        );
    }

    /**
     * Test handleUpdates() functionality.
     *
     * @return void
     */
    public function testHandleUpdates(): void
    {
        $populator = new PopulatorStub();

        $handler = new TransformableHandlerStub(null, [stdClass::class]);
        $handlers = new RegisteredSearchHandlerStub([$handler]);
        $manager = $this->getManager($handlers, null, $populator);

        $objects = [
            new stdClass(),
            new class
            {
            },
        ];

        $manager->handleUpdates(stdClass::class, '_new', $objects);

        $expectedCalls = [
            'Tests\LoyaltyCorp\Search\Stubs\PopulatorStub::populateWith' => [
                [
                    'handler' => $handler,
                    'indexSuffix' => '_new',
                    'objects' => $objects,
                ],
            ],
        ];

        self::assertSame($expectedCalls, $populator->getCalls());
    }

    /**
     * Test handleUpdates() functionality.
     *
     * @return void
     */
    public function testHandleUpdatesNotSearchable(): void
    {
        $populator = new PopulatorStub();

        $handlers = new RegisteredSearchHandlerStub([new TransformableHandlerStub()]);
        $manager = $this->getManager($handlers, null, $populator);

        // Test an unsupported class doesn't do anything
        $manager->handleUpdates(stdClass::class, '_new', []);
        self::assertEmpty($populator->getCalls());
    }

    /**
     * Test handleUpdates() functionality when no transformations occur.
     *
     * @return void
     */
    public function testHandleUpdatesWhenNoTransformationsOccur(): void
    {
        $stub = new ClientStub();
        $handlers = new RegisteredSearchHandlerStub([new TransformableHandlerStub()]);
        $manager = $this->getManager($handlers, $this->createClient($stub));

        // Tests whats going to happen when handleUpdates is called with objects that result
        // in no transformations
        $manager->handleUpdates(stdClass::class, '', [
            new EntityStub()
        ]);

        self::assertNull($stub->getBulkParameters());
    }

    /**
     * Instantiate an ElasticSearch client.
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
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface|null $populator
     *
     * @return \LoyaltyCorp\Search\Manager
     */
    private function getManager(
        RegisteredSearchHandlerStub $handlers,
        ?ClientInterface $client = null,
        ?PopulatorInterface $populator = null
    ): Manager {
        return new Manager(
            $handlers,
            $client ?? $this->createClient(),
            $populator ?? new PopulatorStub(),
        );
    }
}
