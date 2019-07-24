<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use EoneoPay\Externals\Logger\Logger;
use Illuminate\Contracts\Foundation\Application;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Manager;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
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
        self::assertInstanceOf(Manager::class, $application->make(ManagerInterface::class));
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
            EntityManagerHelperInterface::class,
            ManagerInterface::class
        ], (new SearchServiceProvider(new ApplicationStub()))->provides());
    }

    /**
     * Test handlers are correctly filtered by service provider
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException If item requested from container doesn't exist
     */
    public function testHandlerFiltering(): void
    {
        $application = $this->createApplication();

        // Tag handler for service provider
        $application->tag([HandlerStub::class], ['search_handler']);

        // Run registration
        (new SearchServiceProvider($application))->register();

        // Load manager from interface
        $manager = $application->make(ManagerInterface::class);

        // Test stubs
        self::assertFalse($manager->isSearchable(NotSearchableStub::class));
        self::assertTrue($manager->isSearchable(SearchableStub::class));
    }

    /**
     * Create configured application instance for service provider testing
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    private function createApplication(): Application
    {
        $application = new ApplicationStub();

        // Bind logger to container so app->make on interface works
        $application->singleton(LoggerInterface::class, static function (): LoggerInterface {
            return new Logger();
        });

        return $application;
    }
}
