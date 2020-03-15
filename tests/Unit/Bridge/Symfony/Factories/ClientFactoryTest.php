<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Factories;

use Elasticsearch\Client as BaseClient;
use Eonx\TestUtils\Stubs\Eonx\Externals\Logger\ExternalsLoggerStub;
use LoyaltyCorp\Search\Bridge\Symfony\Factories\ClientFactory;
use LoyaltyCorp\Search\Client;
use ReflectionClass;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\ClientBulkResponseHelperStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Factories\ClientFactory
 */
final class ClientFactoryTest extends UnitTestCase
{
    /**
     * Test create client with default value.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreateClient(): void
    {
        $expectedHelper = new ClientBulkResponseHelperStub();
        $client = (new ClientFactory(new ClientBulkResponseHelperStub(), new ExternalsLoggerStub()))->create();

        // Use reflection to assert private properties are set properly.
        $class = new ReflectionClass(Client::class);

        $actualHelper = $class->getProperty('bulkResponseHelper');
        $actualHelper->setAccessible(true);

        $actualElasticClient = $class->getProperty('elastic');
        $actualElasticClient->setAccessible(true);

        self::assertEquals($expectedHelper, $actualHelper->getValue($client));
        self::assertInstanceOf(BaseClient::class, $actualElasticClient->getValue($client));
    }
}
