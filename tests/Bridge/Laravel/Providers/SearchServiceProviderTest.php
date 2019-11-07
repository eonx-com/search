<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use EoneoPay\Externals\HttpClient\Client as HttpClient;
use EoneoPay\Externals\HttpClient\ExceptionHandler;
use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface as HttpClientInterface;
use EoneoPay\Externals\HttpClient\Interfaces\ExceptionHandlerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Illuminate\Contracts\Foundation\Application;
use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteDataListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Indexer\MappingHelperInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\RequestProxyFactoryInterface;
use LoyaltyCorp\Search\Interfaces\ResponseFactoryInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Manager;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\RequestProxyFactory;
use LoyaltyCorp\Search\ResponseFactory;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use LoyaltyCorp\Search\Workers\EntityDeleteWorker;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for testing all services
 */
final class SearchServiceProviderTest extends TestCase
{
    /**
     * Test register binds manager into container.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException If item requested from container doesn't exist
     */
    public function testContainerBindings(): void
    {
        $application = $this->createApplication();

        // Run registration
        (new SearchServiceProvider($application))->register();

        $services = [
            AccessPopulatorInterface::class => AnonymousAccessPopulator::class,
            ClientInterface::class => Client::class,
            ClientBulkResponseHelperInterface::class => ClientBulkResponseHelper::class,
            EntityManagerHelperInterface::class => EntityManagerHelper::class,
            IndexNameTransformerInterface::class => DefaultIndexNameTransformer::class,
            IndexerInterface::class => Indexer::class,
            ManagerInterface::class => Manager::class,
            MappingHelperInterface::class => AccessTokenMappingHelper::class,
            PopulatorInterface::class => Populator::class,
            RegisteredSearchHandlerInterface::class => RegisteredSearchHandler::class,
            RequestProxyFactoryInterface::class => RequestProxyFactory::class,
            ResponseFactoryInterface::class => ResponseFactory::class,
            EntityDeleteDataListener::class => EntityDeleteDataListener::class,
            EntityDeleteWorker::class => EntityDeleteWorker::class,
            EntityUpdateListener::class => EntityUpdateListener::class,
        ];

        foreach ($services as $abstract => $concrete) {
            $service = $application->make($abstract);

            self::assertInstanceOf($concrete, $service);
        }
    }

    /**
     * Ensure the Doctrine EntityManager resolution will throw our Exception if it cannot resolve.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testDoctrineEntityManagerResolutionThrowsException(): void
    {
        $application = $this->createApplication();
        $application->singleton('registry', ClientStub::class);

        (new SearchServiceProvider($application))->register();

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Could not resolve Entity Manager from application container');

        $application->make(EntityManagerHelperInterface::class);
    }

    /**
     * Test handlers are correctly filtered by service provider.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException If item requested from container doesn't exist
     */
    public function testRegisteredSearchHandlerInterfaceFiltering(): void
    {
        $application = $this->createApplication();

        // Tag handler for service provider
        $application->tag(
            [TransformableSearchHandlerStub::class, NotSearchableStub::class, NonDoctrineHandlerStub::class],
            ['search_handler']
        );
        // The only available handler is when using get should beHandlerStub
        $expected = [new TransformableSearchHandlerStub(), new NonDoctrineHandlerStub()];

        $serviceProvider = new SearchServiceProvider($application);
        $serviceProvider->register();

        $registeredHandlers = $application->make(RegisteredSearchHandlerInterface::class);

        self::assertEquals($expected, $registeredHandlers->getAll());
    }

    /**
     * Overridden to bind required dependencies.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function createApplication(): Application
    {
        $app = parent::createApplication();
        $app->bind(HttpClientInterface::class, HttpClient::class);
        $app->bind(ExceptionHandlerInterface::class, ExceptionHandler::class);
        $app->bind(GuzzleClientInterface::class, GuzzleClient::class);

        return $app;
    }
}
