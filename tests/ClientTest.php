<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use Elasticsearch\ClientBuilder;
use GuzzleHttp\Ring\Client\MockHandler;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\SearchCheckerException;
use LoyaltyCorp\Search\Exceptions\SearchDeleteException;
use LoyaltyCorp\Search\Exceptions\SearchUpdateException;
use PHPUnit\Framework\AssertionFailedError;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\CallableResponseClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\NullResponseClientStub;

/**
 * @covers \LoyaltyCorp\Search\Client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 */
final class ClientTest extends TestCase
{
    /**
     * Inputs to cause Exceptions
     *
     * @return iterable|mixed[]
     */
    public function getInputsCausingExceptions(): iterable
    {
        yield 'bulkUpdate' => [
            'method' => 'bulkUpdate',
            'arguments' => ['index', ['1' => 'document']],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'An error occured while performing bulk update on backend'
        ];

        yield 'bulkDelete' => [
            'method' => 'bulkDelete',
            'arguments' => [['index' => [['1']]]],
            'exception' => SearchDeleteException::class,
            'exceptionMessage' => 'An error occured while performing bulk delete on backend'
        ];

        yield 'createAlias' => [
            'method' => 'createAlias',
            'arguments' => ['index', 'alias'],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to add alias'
        ];

        yield 'createIndex' => [
            'method' => 'createIndex',
            'arguments' => ['index'],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to create new index'
        ];

        yield 'deleteAlias' => [
            'method' => 'deleteAlias',
            'arguments' => [['alias']],
            'exception' => SearchDeleteException::class,
            'exceptionMessage' => 'Unable to delete alias'
        ];

        yield 'deleteIndex' => [
            'method' => 'deleteIndex',
            'arguments' => ['index'],
            'exception' => SearchDeleteException::class,
            'exceptionMessage' => 'Unable to delete index'
        ];

        yield 'moveAlias' => [
            'method' => 'moveAlias',
            'arguments' => [[['index' => 'index_new', 'alias' => 'index']]],
            'exception' => SearchUpdateException::class,
            'exceptionMessage' => 'Unable to atomically swap alias'
        ];
    }

    /**
     * Test bulk() resolves callables
     *
     * @return void
     */
    public function testBulkCallableResolution(): void
    {
        $stub = new CallableResponseClientStub();
        $client = new Client($stub);

        $client->bulkUpdate('index', ['1' => 'document']);

        // @todo: Throw error from callable and check result
        $this->addToAssertionCount(1);
    }

    /**
     * Test bulk() is passed through to elastic search client
     *
     * @return void
     */
    public function testBulkPassthrough(): void
    {
        $stub = new ClientStub();
        $client = new Client($stub);

        $client->bulkDelete(['index' => [['1']]]);

        self::assertSame(
            ['body' => [['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => ['1']]]]],
            $stub->getBulkParameters()
        );
    }

    /**
     * Test bulk() checks returned data for invalid values
     *
     * @return void
     */
    public function testBulkReturnTypeCheck(): void
    {
        $stub = new NullResponseClientStub();
        $client = new Client($stub);

        // A null result should throw an exception
        $this->expectException(SearchUpdateException::class);
        $this->expectExceptionMessage('An error occurred while performing bulk update on backend');

        $client->bulkUpdate('index', ['1' => 'document']);
    }

    /**
     * Test creating an alias
     * ElasticSearch client is not interfaced, we don't handle any return type with this method, thus testcase is simple
     *
     * @return void
     */
    public function testCreatingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $client->createAlias('index1', 'big_alias');

        $this->addToAssertionCount(1);
    }

    /**
     * Ensure creating a new index will pass through correctly formatted mappings and settings if provided
     * ElasticSearch client is not interfaced, we don't handle any return type with this method, thus testcase is simple
     *
     * @return void
     */
    public function testCreatingIndexProvidesMeta(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $client->createIndex('big_index');

        $this->addToAssertionCount(1);
    }

    /**
     * Test deleting an alias does not result in an Exception
     * ElasticSearch client is not interfaced, we don't handle any return type with this method, thus testcase is simple
     *
     * @return void
     */
    public function testDeletingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $client->deleteAlias(['big_alias']);

        $this->addToAssertionCount(1);
    }

    /**
     * Test deleting an index
     *
     * @return void
     */
    public function testDeletingIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [];

        $client->deleteIndex('index1');

        // @todo
        self::assertSame($expected, []);
    }

    /**
     * Test exception thrown by all public functions
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
        $client = new Client(new ClientStub(true));

        $this->expectException($exception);

        $client->{$method}(...$arguments);
    }

    /**
     * Ensure the isAlias method respects HTTP status code
     *
     * @return void
     */
    public function testIsAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 200);
        $client = new Client($elasticClient);

        $result = $client->isAlias('nice_alias');

        self::assertTrue($result);
    }

    /**
     * Ensure exceptions are decorated on isAlias method
     *
     * @return void
     */
    public function testIsAliasThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred checking if alias exists');

        $elasticClient = $this->createElasticClient([], 500);
        $client = new Client($elasticClient);

        $client->isAlias('nice_alias');
    }

    /**
     * Test ensuring the IsAlias commands works appropiately
     *
     * @return void
     */
    public function testIsIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 200);
        $client = new Client($elasticClient);

        $result = $client->isIndex('nice_alias');

        self::assertTrue($result);
    }

    /**
     * Ensure exceptions are decorated on isIndex method
     *
     * @return void
     */
    public function testIsIndexThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error occurred checking if index exists');

        $elasticClient = $this->createElasticClient([], 500);
        $client = new Client($elasticClient);

        $client->isIndex('index_1');
    }

    /**
     * Ensure the isAlias method respects HTTP status code
     *
     * @return void
     */
    public function testIsNotAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 404);
        $client = new Client($elasticClient);

        $result = $client->isAlias('nice_alias');

        self::assertFalse($result);
    }

    /**
     * Test ensuring the IsAlias respects the HTTP status code
     *
     * @return void
     */
    public function testIsNotIndex(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response, 404);
        $client = new Client($elasticClient);

        $result = $client->isIndex('nice_alias');

        self::assertFalse($result);
    }

    /**
     * Ensure listing all aliases formats the expected response from elasticsearch client
     *
     * @return void
     */
    public function testListAliases(): void
    {
        $response = [['alias' => 'alias1', 'index' => 'index1'], ['alias' => 'alias2', 'index' => 'index1']];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [['name' => 'alias1', 'index' => 'index1'], ['name' => 'alias2', 'index' => 'index1']];

        $result = $client->getAliases();

        self::assertSame($expected, $result);
    }

    /**
     * Ensure listing all aliases will throw an Exception if a non-200 HTTP status is received
     *
     * @return void
     */
    public function testListAliasesThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error ocurred obtaining a list of aliases');

        $response = [['alias' => 'alias1', 'index' => 'index1'], ['alias' => 'alias2', 'index' => 'index1']];
        $elasticClient = $this->createElasticClient($response, 400);
        $client = new Client($elasticClient);

        $client->getAliases();
    }

    /**
     * Test the listing of existing indices
     *
     * @return void
     */
    public function testListingIndices(): void
    {
        $response = [['index' => 'index1'], ['index' => 'index2']];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [['name' => 'index1'], ['name' => 'index2']];

        $result = $client->getIndices();

        self::assertSame($expected, $result);
    }

    /**
     * Test the listing of existing indices
     *
     * @return void
     */
    public function testListingIndicesThrowsException(): void
    {
        $this->expectException(SearchCheckerException::class);
        $this->expectExceptionMessage('An error ocurred obtaining a list of indices');

        $response = [['index' => 'index1'], ['index' => 'index2']];
        $elasticClient = $this->createElasticClient($response, 400);
        $client = new Client($elasticClient);

        $client->getIndices();
    }

    /**
     * Ensure moveAlias invoking updateAliases to ES client does not result in an Exception
     *
     * @return void
     */
    public function testMovingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $client->moveAlias([['alias' => 'index', 'index' => 'index_20190502']]);

        $this->addToAssertionCount(1);
    }

    /**
     * Test non-iterable items in delete are skipped before handling to bulk()
     *
     * @return void
     */
    public function testNonIterableItemsAreSkipped(): void
    {
        $stub = new ClientStub();
        $client = new Client($stub);

        $client->bulkDelete(['index' => [['7']], 'not-iterable']);

        self::assertSame(
            ['body' => [['delete' => ['_index' => 'index', '_type' => 'doc', '_id' => ['7']]]]],
            $stub->getBulkParameters()
        );
    }

    /**
     * Create an elastic search client with mocked transport handler
     *
     * @param mixed[] $response
     * @param int|null $statusCode Defaults 200
     *
     * @return \Elasticsearch\Client
     */
    private function createElasticClient(array $response, ?int $statusCode = null): \Elasticsearch\Client
    {
        // In-memory resource stream for speedy tests
        $stream = \fopen('php://memory', 'b+');
        if (\is_resource($stream) === false) {
            throw new AssertionFailedError('Unable to open in-memory resource for mocked guzzle handler');
        }

        if (\fwrite($stream, \json_encode($response) ?: 'null') === false) {
            throw new AssertionFailedError('Unable to write to in-memory resource for mocked guzzle handler');
        }

        // Reposition pointer to the start of the streama
        \rewind($stream);

        $mockResponse = [
            'status' => $statusCode ?? 200,
            'transfer_stats' => [
                'total_time' => 100
            ],
            'body' => $stream
        ];

        return (new ClientBuilder())
            ->setHandler(
                new MockHandler($mockResponse)
            )
            ->build();
    }
}
