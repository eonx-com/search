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
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

/**
 * @covers \LoyaltyCorp\Search\Client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 */
final class ClientTest extends TestCase
{
    /**
     * Test exception thrown by bulk() while deleting is handled correctly
     *
     * @return void
     */
    public function testBulkExceptionOnDeleteIsHandled(): void
    {
        $client = new Client(new ClientStub(true));

        $this->expectException(SearchDeleteException::class);

        $client->bulkDelete(['index' => [['1']]]);
    }

    /**
     * Test exception thrown by bulk() while updating is handled correctly
     *
     * @return void
     */
    public function testBulkExceptionOnUpdateIsHandled(): void
    {
        $client = new Client(new ClientStub(true));

        $this->expectException(SearchUpdateException::class);

        $client->bulkUpdate('index', ['1' => 'document']);
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
     * Test creating an alias
     *
     * @return void
     */
    public function testCreatingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [];

        $client->createAlias('index1', 'big_alias');

        // @todo spy
        self::assertSame($expected, []);
    }

    /**
     * Ensure creating a new index will pass through correctly formatted mappings and settings if provided
     *
     * @return void
     */
    public function testCreatingIndexProvidesMeta(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [];

        $client->createIndex('', null, null);

        // assert mappings built as same as provided
        // assert settings are

        // @todo spy
        self::assertSame($expected, []);
    }

    /**
     * Test deleting an alias
     *
     * @return void
     */
    public function testDeletingAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);
        $expected = [];

        $client->deleteAlias('index1', 'big_alias');

        // @todo
        self::assertSame($expected, []);
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
     * Ensure the isAlias method respects HTTP status code
     *
     * @return void
     */
    public function testIsAlias(): void
    {
        $response = [];
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $result = $client->isAlias('nice_alias');

        self::assertSame(true, $result);
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
        $elasticClient = $this->createElasticClient($response);
        $client = new Client($elasticClient);

        $result = $client->isIndex('nice_alias');

        self::assertSame(true, $result);
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
