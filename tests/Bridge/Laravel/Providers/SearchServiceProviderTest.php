<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\ObjectTransformerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Manager;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for testing all services
 */
final class SearchServiceProviderTest extends TestCase
{
    /**
     * Test register binds manager into container
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

        // Ensure services are bound
        self::assertInstanceOf(Client::class, $application->make(ClientInterface::class));
        self::assertInstanceOf(Manager::class, $application->make(ManagerInterface::class));
        self::assertInstanceOf(EntityManagerHelper::class, $application->make(EntityManagerHelperInterface::class));
        self::assertInstanceOf(Populator::class, $application->make(PopulatorInterface::class));
        self::assertInstanceOf(
            RegisteredSearchHandler::class,
            $application->make(RegisteredSearchHandlerInterface::class)
        );
        self::assertInstanceOf(
            DefaultIndexNameTransformer::class,
            $application->make(IndexNameTransformerInterface::class)
        );
    }

    /**
     * Test service provider returns manager as a deferred service
     *
     * @return void
     */
    public function testDeferredProvider(): void
    {
        self::assertSame([
            ClientInterface::class,
            IndexerInterface::class,
            ManagerInterface::class,
            PopulatorInterface::class,
            RegisteredSearchHandlerInterface::class
        ], (new SearchServiceProvider(new ApplicationStub()))->provides());
    }

    /**
     * Ensure the Doctrine EntityManager resolution will throw our Exception if it cannot resolve
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testDoctrineEntityManagerResolutionThrowsException(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Could not resolve Entity Manager from application container');

        $application = $this->createApplication();
        $application->singleton('registry', ClientStub::class);

        (new SearchServiceProvider($application))->register();

        $application->make(EntityManagerHelperInterface::class);
    }

    /**
     * Test handlers are correctly filtered by service provider
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
}
