<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Providers;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use EoneoPay\Externals\HttpClient\Client as HttpClient;
use EoneoPay\Externals\HttpClient\ExceptionHandler;
use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface as HttpClientInterface;
use EoneoPay\Externals\HttpClient\Interfaces\ExceptionHandlerInterface;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use EoneoPay\Externals\Logger\Logger;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use Eonx\TestUtils\Stubs\Vendor\Doctrine\ORM\EntityManagerStub;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\Indexer\MappingHelperInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\RequestProxyFactoryInterface;
use LoyaltyCorp\Search\Interfaces\ResponseFactoryInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\RequestProxyFactory;
use LoyaltyCorp\Search\ResponseFactory;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use LoyaltyCorp\Search\UpdateProcessor;
use LoyaltyCorp\Search\Workers\EntityUpdateWorker;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\EventDispatcherStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\RegistryStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub as EoneoPayEntityManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for testing all services
 */
final class SearchServiceProviderTest extends UnitTestCase
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

        $application->singleton(
            EventDispatcherInterface::class,
            EventDispatcherStub::class
        );

        // Run registration
        (new SearchServiceProvider($application))->register();

        $services = [
            AccessPopulatorInterface::class => AnonymousAccessPopulator::class,
            ClientInterface::class => Client::class,
            ClientBulkResponseHelperInterface::class => ClientBulkResponseHelper::class,
            EntityUpdateListener::class => EntityUpdateListener::class,
            EntityUpdateWorkerInterface::class => EntityUpdateWorker::class,
            IndexNameTransformerInterface::class => DefaultIndexNameTransformer::class,
            IndexerInterface::class => Indexer::class,
            MappingHelperInterface::class => AccessTokenMappingHelper::class,
            PopulatorInterface::class => Populator::class,
            RegisteredSearchHandlersInterface::class => RegisteredSearchHandlers::class,
            RequestProxyFactoryInterface::class => RequestProxyFactory::class,
            ResponseFactoryInterface::class => ResponseFactory::class,
            UpdateProcessorInterface::class => UpdateProcessor::class,
        ];

        foreach ($services as $abstract => $concrete) {
            $service = $application->make($abstract);

            self::assertInstanceOf($concrete, $service);
        }
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
            [TransformableHandlerStub::class, stdClass::class, NonDoctrineHandlerStub::class],
            ['search_handler']
        );

        $expected = [new TransformableHandlerStub(), new NonDoctrineHandlerStub()];

        $serviceProvider = new SearchServiceProvider($application);
        $serviceProvider->register();

        $registeredHandlers = $application->make(RegisteredSearchHandlersInterface::class);

        self::assertEquals($expected, $registeredHandlers->getAll());
    }

    /**
     * Overridden to bind required dependencies.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function createApplication(): Application
    {
        $app = new ApplicationStub();

        // Bind logger to container so app->make on interface works
        $app->singleton(LoggerInterface::class, static function (): LoggerInterface {
            return new Logger();
        });

        // Bind Doctrine EntityManager to container so app->make on interface works
        $app->singleton(DoctrineEntityManagerInterface::class, static function (): EntityManagerStub {
            return new EntityManagerStub();
        });

        // Bind eoneopay EntityManager to container so app->make on interface works
        $app->singleton(EoneoPayEntityManagerInterface::class, static function (): EoneoPayEntityManagerStub {
            return new EoneoPayEntityManagerStub();
        });

        // Bind illuminate Dispatcher to container so app->make on interface works
        $app->singleton(
            IlluminateDispatcherInterface::class,
            static function (): IlluminateDispatcherInterface {
                return new IlluminateDispatcher();
            }
        );

        $app->singleton('registry', RegistryStub::class);
        $app->bind(HttpClientInterface::class, HttpClient::class);
        $app->bind(ExceptionHandlerInterface::class, ExceptionHandler::class);
        $app->bind(GuzzleClientInterface::class, GuzzleClient::class);

        return $app;
    }
}
