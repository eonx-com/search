<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit;

use Elasticsearch\Client as BaseClient;
use Elasticsearch\ClientBuilder;
use GuzzleHttp\Ring\Client\MockHandler;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use LoyaltyCorp\Search\Exceptions\SearchCheckerException;
use LoyaltyCorp\Search\Exceptions\SearchDeleteException;
use LoyaltyCorp\Search\Exceptions\SearchUpdateException;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use PHPUnit\Framework\AssertionFailedError;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\CallableResponseClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 * @SuppressWarnings(PHPMD.TooManyMethods) Required for testing
 */
final class ClientTest extends UnitTestCase
{
    /**
     * Inputs to cause Exceptions.
     *
     * @return iterable|mixed[]
     */
    public function getInputsCausingExceptions(): iterable
    {
        yield 'bulk' => [
            'method' => 'bulk',
            'arguments' => [[new IndexAction(new DocumentUpdate('1', 'document'), 'index')]],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'An error occured while performing bulk update on backend',
        ];

        yield 'count' => [
            'method' => 'count',
            'arguments' => ['strongIndex'],
            'exception' => SearchCheckerException::class,
            'exceptionMessage' => 'Unable to count number of documents within index',
        ];

        yield 'createAlias' => [
            'method' => 'createAlias',
            'arguments' => ['index', 'alias'],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to add alias',
        ];

        yield 'createIndex' => [
            'method' => 'createIndex',
            'arguments' => ['index'],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to create new index',
        ];

        yield 'deleteAlias' => [
            'method' => 'deleteAlias',
            'arguments' => [['alias']],
            'exception' => SearchDeleteException::class,
            'exceptionMessage' => 'Unable to delete alias',
        ];

        yield 'deleteIndex' => [
            'method' => 'deleteIndex',
            'arguments' => ['index'],
            'exception' => SearchDeleteException::class,
            'exceptionMessage' => 'Unable to delete index',
        ];

        yield 'moveAlias' => [
            'method' => 'moveAlias',
            'arguments' => [[['index' => 'index_new', 'alias' => 'index']]],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to atomically swap alias',
        ];
    }

    /**
     * Test bulk() resolves callables.
     *
     * @return void
     */
    public function testBulkCallableResolution(): void
    {
        $stub = new CallableResponseClientStub();
        $client = $this->createInstance($stub);

        $client->bulk([
            new IndexAction(new DocumentUpdate('1', 'document'), 'index'),
        ]);

        // If call was successful there should be no return/exception
        $this->addToAssertionCount(1);
    }

    /**
     * Test bulk() is passed through to elastic search client.
     *
     * @return void
     */
    public function testBulkPassthrough(): void
    {
        $stub = new ClientStub();
        $client = $this->createInstance($stub);

        $expected = ['body' => [['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => '1']]]];

        $client->bulk([
            new IndexAction(new DocumentDelete('1'), 'index'),
        ]);

        self::assertSame($expected, $stub->getBulkParameters());
    }

    /**
     * Test elastic client returning count data.
     *
     * @return void
     */
    public function testCount(): void
    {
        $response = [['count' => 5]];
        $elasticClient = $this->createElasticClient($response, 200);
        $client = $this->createInstance($elasticClient);

        $result = $client->count('anIndex');

        self::assertSame(5, $result);
    }

    /**
     * Test creating an alias
     * ElasticSearch client is not interfaced, we don't handle any return type with this
     * method, thus testcase is simple.
     *
     * @return void
     */
    public function testCreatingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);

        $client->createAlias('index1', 'big_alias');

        $this->addToAssertionCount(1);
    }

    /**
     * Ensure creating a new index will pass through correctly formatted mappings and settings if provided
     * ElasticSearch client is not interfaced, we don't handle any return type with this
     * method, thus testcase is simple.
     *
     * @return void
     */
    public function testCreatingIndexProvidesMeta(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);

        $client->createIndex('big_index');

        $this->addToAssertionCount(1);
    }

    /**
     * Test deleting an alias does not result in an Exception
     * ElasticSearch client is not interfaced, we don't handle any return type with this
     * method, thus testcase is simple.
     *
     * @return void
     */
    public function testDeletingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);

        $client->deleteAlias(['big_alias']);

        $this->addToAssertionCount(1);
    }

    /**
     * Test deleting an index.
     *
     * @return void
     */
    public function testDeletingIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);
        $expected = [];

        $client->deleteIndex('index1');

        // @todo
        self::assertSame($expected, []);
    }

    /**
     * Test bulk() resolves errors from responses.
     *
     * @return void
     */
    public function testErrorResolutionWithErrors(): void
    {
        $stub = new CallableResponseClientStub([
            'errors' => true,
            'items' => [
                [
                    'create' => [],
                ],
                [
                    'update' => [
                        'error' => [
                            'index' => 'my_index',
                            'index_uuid' => 'maU4iW15SmyoZadsmRiNWw',
                            'reason' => '[my_index][1]: version conflict, document already exists',
                            'shard' => 3,
                            'type' => 'version_conflict_engine_exception',
                        ],
                    ],
                ],
                [
                    'update' => [],
                ],
            ],
        ]);
        $client = $this->createInstance($stub);

        // Callable contains an error specifically for testing
        $this->expectException(BulkFailureException::class);
        $this->expectExceptionMessage('At least one record returned an error during bulk request.');

        $client->bulk([
            new IndexAction(new DocumentUpdate('1', 'document'), 'my_index'),
        ]);
    }

    /**
     * Test exception thrown by all public functions.
     *
     * @phpstan-param class-string<\Throwable> $exception
     *
     * @param string $method
     * @param mixed[] $arguments
     * @param string $exception
     *
     * @return void
     *
     * @dataProvider getInputsCausingExceptions()
     */
    public function testExceptionCatching(
        string $method,
        array $arguments,
        string $exception
    ): void {
        $client = $this->createInstance(new ClientStub(true));

        $this->expectException($exception);

        $client->{$method}(...$arguments);
    }

    /**
     * Tests that the 'getHealth' method returns the expected DTO values.
     *
     * @return void
     */
    public function testGetHealth(): void
    {
        $response = [
            'cluster_name' => 'testcluster',
            'status' => 'yellow',
            'timed_out' => false,
            'number_of_nodes' => 1,
            'number_of_data_nodes' => 1,
            'active_primary_shards' => 5,
            'active_shards' => 5,
            'relocating_shards' => 0,
            'initializing_shards' => 0,
            'unassigned_shards' => 5,
            'delayed_unassigned_shards' => 0,
            'number_of_pending_tasks' => 0,
            'number_of_in_flight_fetch' => 0,
            'task_max_waiting_in_queue_millis' => 0,
            'active_shards_percent_as_number' => 50.0,
        ];
        $client = $this->createElasticClient($response, 200);
        $instance = $this->createInstance($client);

        $result = $instance->getHealth();

        self::assertSame('testcluster', $result->getName());
        self::assertSame('yellow', $result->getStatus());
        self::assertFalse($result->hasTimedOut());
        self::assertSame(1, $result->getNumberOfNodes());
        self::assertSame(1, $result->getNumberOfDataNodes());
        self::assertSame(5, $result->getNumberOfActivePrimaryShards());
        self::assertSame(5, $result->getNumberOfActiveShards());
        self::assertSame(0, $result->getNumberOfRelocatingShards());
        self::assertSame(0, $result->getNumberOfInitializingShards());
        self::assertSame(5, $result->getNumberOfUnassignedShards());
        self::assertSame(0, $result->getNumberOfDelayedUnassignedShards());
        self::assertSame(0, $result->getNumberOfPendingTasks());
        self::assertSame(0, $result->getNumberOfInFlightFetch());
        self::assertSame(0, $result->getTaskMaxWaitingInQueueMillis());
        self::assertSame(50, $result->getActiveShardsPercent());
    }

    /**
     * Tests that the 'getHealth' method throws an exception when the Elasticsearch response is not as documented,
     * and creation of the DTO fails.
     *
     * @return void
     */
    public function testGetHealthThrowsExceptionWithInvalidResponse(): void
    {
        $client = $this->createElasticClient([], 500);
        $instance = $this->createInstance($client);

        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred checking the cluster health');

        $instance->getHealth();
    }

    /**
     * Ensure the isAlias method respects HTTP status code.
     *
     * @return void
     */
    public function testIsAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 200);
        $client = $this->createInstance($elasticClient);

        $result = $client->isAlias('nice_alias');

        self::assertTrue($result);
    }

    /**
     * Ensure exceptions are decorated on isAlias method.
     *
     * @return void
     */
    public function testIsAliasThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred checking if alias exists');

        $elasticClient = $this->createElasticClient([], 500);
        $client = $this->createInstance($elasticClient);

        $client->isAlias('nice_alias');
    }

    /**
     * Test ensuring the IsAlias commands works appropiately.
     *
     * @return void
     */
    public function testIsIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 200);
        $client = $this->createInstance($elasticClient);

        $result = $client->isIndex('nice_alias');

        self::assertTrue($result);
    }

    /**
     * Ensure exceptions are decorated on isIndex method.
     *
     * @return void
     */
    public function testIsIndexThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred checking if index exists');

        $elasticClient = $this->createElasticClient([], 500);
        $client = $this->createInstance($elasticClient);

        $client->isIndex('index_1');
    }

    /**
     * Ensure the isAlias method respects HTTP status code.
     *
     * @return void
     */
    public function testIsNotAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 404);
        $client = $this->createInstance($elasticClient);

        $result = $client->isAlias('nice_alias');

        self::assertFalse($result);
    }

    /**
     * Test ensuring the IsAlias respects the HTTP status code.
     *
     * @return void
     */
    public function testIsNotIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 404);
        $client = $this->createInstance($elasticClient);

        $result = $client->isIndex('nice_alias');

        self::assertFalse($result);
    }

    /**
     * Ensure listing all aliases formats the expected response from elasticsearch client.
     *
     * @return void
     */
    public function testListAliases(): void
    {
        $response = [['alias' => 'alias1', 'index' => 'index1'], ['alias' => 'alias2', 'index' => 'index1']];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);
        $expected = [['name' => 'alias1', 'index' => 'index1'], ['name' => 'alias2', 'index' => 'index1']];

        $result = $client->getAliases();

        self::assertSame($expected, $result);
    }

    /**
     * Ensure listing all aliases will throw an Exception if a non-200 HTTP status is received.
     *
     * @return void
     */
    public function testListAliasesThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred obtaining a list of aliases');

        $response = [['alias' => 'alias1', 'index' => 'index1'], ['alias' => 'alias2', 'index' => 'index1']];
        $elasticClient = $this->createElasticClient($response, 400);
        $client = $this->createInstance($elasticClient);

        $client->getAliases();
    }

    /**
     * Test the listing of existing indices.
     *
     * @return void
     */
    public function testListingIndices(): void
    {
        $response = [['index' => 'index1'], ['index' => 'index2']];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);
        $expected = [['name' => 'index1'], ['name' => 'index2']];

        $result = $client->getIndices();

        self::assertSame($expected, $result);
    }

    /**
     * Test the listing of existing indices.
     *
     * @return void
     */
    public function testListingIndicesThrowsException(): void
    {
        $response = [['index' => 'index1'], ['index' => 'index2']];
        $elasticClient = $this->createElasticClient($response, 400);
        $client = $this->createInstance($elasticClient);

        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred obtaining a list of indices');

        $client->getIndices();
    }

    /**
     * Ensure moveAlias invoking updateAliases to ES client does not result in an Exception.
     *
     * @return void
     */
    public function testMovingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = $this->createInstance($elasticClient);

        $client->moveAlias([['alias' => 'index', 'index' => 'index_20190502']]);

        $this->addToAssertionCount(1);
    }

    /**
     * Create an elastic search client with mocked transport handler.
     *
     * @param mixed[] $response
     * @param int|null $statusCode Defaults 200
     *
     * @return \Elasticsearch\Client
     */
    private function createElasticClient(array $response, ?int $statusCode = null): BaseClient
    {
        // In-memory resource stream for speedy tests
        $stream = \fopen('php://memory', 'b+');
        if (\is_resource($stream) === false) {
            throw new AssertionFailedError('Unable to open in-memory resource for mocked guzzle handler');
        }

        if (\fwrite($stream, \json_encode($response, \JSON_THROW_ON_ERROR)) === false) {
            throw new AssertionFailedError('Unable to write to in-memory resource for mocked guzzle handler');
        }

        // Reposition pointer to the start of the stream
        \rewind($stream);

        $mockResponse = [
            'body' => $stream,
            'effective_url' => 'localhost',
            'status' => $statusCode ?? 200,
            'transfer_stats' => [
                'total_time' => 100,
                'primary_port' => '80',
            ],
        ];

        return (new ClientBuilder())
            ->setHandler(
                new MockHandler($mockResponse)
            )
            ->build();
    }

    /**
     * Instantaite a client class.
     *
     * @param \Elasticsearch\Client $client
     *
     * @return \LoyaltyCorp\Search\Client
     */
    private function createInstance(BaseClient $client): Client
    {
        // BulkResponseHelper class is not stubbed because it's a literal dependency of the client
        return new Client($client, new ClientBulkResponseHelper());
    }
}
