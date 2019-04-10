<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\SearchDeleteException;
use LoyaltyCorp\Search\Exceptions\SearchUpdateException;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

/**
 * @covers \LoyaltyCorp\Search\Client
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

        self::assertSame(['body' => [['delete' => ['_index' => 'index', '_id' => ['1']]]]], $stub->getBulkParameters());
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

        self::assertSame(['body' => [['delete' => ['_index' => 'index', '_id' => ['7']]]]], $stub->getBulkParameters());
    }
}
