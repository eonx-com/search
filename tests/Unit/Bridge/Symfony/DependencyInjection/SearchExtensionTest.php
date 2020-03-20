<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\DependencyInjection;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexCleanCommand;
use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexCreateCommand;
use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexFillCommand;
use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection\SearchExtension;
use LoyaltyCorp\Search\Bridge\Symfony\Factories\ClientFactory;
use LoyaltyCorp\Search\Bridge\Symfony\Factories\RegisteredSearchHandlersFactory;
use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\ClientFactoryInterface;
use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\RegisteredSearchHandlersFactoryInterface;
use LoyaltyCorp\Search\Bridge\Symfony\Listeners\BatchOfUpdatesListener;
use LoyaltyCorp\Search\Bridge\Symfony\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection\SearchExtension
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 */
final class SearchExtensionTest extends UnitTestCase
{
    /**
     * Test commands are loaded.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testCommandsAreLoaded(): void
    {
        $container = new ContainerBuilder();

        (new SearchExtension())->load([], $container);

        $commands = [
            SearchIndexCleanCommand::class,
            SearchIndexCreateCommand::class,
            SearchIndexFillCommand::class,
            SearchIndexLiveCommand::class,
        ];

        $taggedServices = \array_keys($container->findTaggedServiceIds('console.command'));

        foreach ($commands as $commandClass) {
            $definition = $container->getDefinition($commandClass);

            self::assertNotNull($definition);
            self::assertNotNull($definition->getTag('console.command'));
            self::assertContains($commandClass, $taggedServices);
        }
    }

    /**
     * Test listeners are loaded.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testListenersAreLoaded(): void
    {
        $container = new ContainerBuilder();

        (new SearchExtension())->load([], $container);

        $listeners = [
            BatchOfUpdatesEvent::class => BatchOfUpdatesListener::class,
            EntityChangeEvent::class => EntityUpdateListener::class,
        ];

        $taggedServices = \array_keys($container->findTaggedServiceIds('kernel.event_listener'));

        foreach ($listeners as $event => $listener) {
            $definition = $container->getDefinition($listener);

            self::assertNotNull($definition);
            self::assertContains($listener, $taggedServices);
            self::assertContains($event, $definition->getTag('kernel.event_listener')[0] ?? []);
        }
    }

    /**
     * Test load calls expected methods and assert class bindings through definitions.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testServicesAreLoaded(): void
    {
        $container = new ContainerBuilder();

        (new SearchExtension())->load([], $container);

        $services = [
            AccessPopulatorInterface::class => AnonymousAccessPopulator::class,
            ClientFactoryInterface::class => ClientFactory::class,
            ClientInterface::class => Client::class,
            ClientBulkResponseHelperInterface::class => ClientBulkResponseHelper::class,
            EntityUpdateWorkerInterface::class => EntityUpdateWorker::class,
            IndexNameTransformerInterface::class => DefaultIndexNameTransformer::class,
            IndexerInterface::class => Indexer::class,
            MappingHelperInterface::class => AccessTokenMappingHelper::class,
            PopulatorInterface::class => Populator::class,
            RegisteredSearchHandlersFactoryInterface::class => RegisteredSearchHandlersFactory::class,
            RegisteredSearchHandlersInterface::class => RegisteredSearchHandlers::class,
            RequestProxyFactoryInterface::class => RequestProxyFactory::class,
            ResponseFactoryInterface::class => ResponseFactory::class,
            UpdateProcessorInterface::class => UpdateProcessor::class,
        ];

        $withFactoryServices = [
            RegisteredSearchHandlersInterface::class => RegisteredSearchHandlersFactoryInterface::class,
            ClientInterface::class => ClientFactoryInterface::class,
        ];

        foreach ($services as $interface => $service) {
            $definition = $container->getDefinition($interface);

            self::assertNotNull($definition);

            if (isset($withFactoryServices[$interface]) === true) {
                self::assertEquals($withFactoryServices[$interface], (string)($definition->getFactory()[0] ?? null));

                continue;
            }

            self::assertEquals($service, $definition->getClass());
        }
    }
}
