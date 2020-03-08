<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Factories;

use Elasticsearch\Client as BaseClient;
use LoyaltyCorp\Search\Bridge\Symfony\Factories\ClientFactory;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\ClientBulkResponseHelperStub;
use Tests\LoyaltyCorp\Search\Stubs\LoggerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Factories\ClientFactory
 */
final class ClientFactoryTest extends UnitTestCase
{
    /**
     * Test create client with default value.
     */
    public function testCreateClient(): void
    {
        $bulkResponseHelper = new ClientBulkResponseHelperStub();
        $client = (new ClientFactory(new ClientBulkResponseHelperStub(), new LoggerStub()))->create();

        self::assertEquals($bulkResponseHelper, $this->getPrivatePropertyValue($client, 'bulkResponseHelper'));
        self::assertInstanceOf(BaseClient::class, $this->getPrivatePropertyValue($client, 'elastic'));
    }
}
