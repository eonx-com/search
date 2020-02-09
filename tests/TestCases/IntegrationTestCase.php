<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\TestCases;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface;
use Eonx\TestUtils\TestCases\Integration\DoctrineORMTestCase;
use Illuminate\Container\Container;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\BatchOfUpdatesListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\ClientStub;

abstract class IntegrationTestCase extends DoctrineORMTestCase
{
    /**
     * @var \Illuminate\Container\Container
     */
    private $container;

    /**
     * Builds search services.
     *
     * @param \Psr\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
     */
    protected function buildServices(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager
    ): void {
        $container = new Container();
        $container->instance(EventDispatcherInterface::class, $dispatcher);
        $container->instance(EntityManagerInterface::class, $entityManager);

        SearchServiceProvider::registerInContainer($container);

        // Override the real search client to a stub
        $container->instance(
            'search_elasticsearch_client',
            new ClientStub()
        );

        // Register and tag all handlers
        foreach ($this->getRegisteredHandlers() as $idx => $handler) {
            $abstract = $handler.$idx;

            $container->singleton($abstract, $handler);
            $container->tag($abstract, ['search_handler']);
        }

        // Initialise the required listeners.
        $dispatcher->addListener(
            EntityChangeEvent::class,
            static function (EntityChangeEvent $event) use ($container): void {
                $listener = new EntityUpdateListener($container->get(EntityUpdateWorkerInterface::class));
                $listener->handle($event);
            }
        );

        $dispatcher->addListener(
            BatchOfUpdatesEvent::class,
            static function (BatchOfUpdatesEvent $event) use ($container): void {
                $listener = new BatchOfUpdatesListener($container->get(UpdateProcessorInterface::class));
                $listener->handle($event);
            }
        );

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function createDoctrineEntityManager(): EntityManagerInterface
    {
        $entityManager = parent::createDoctrineEntityManager();

        $dispatcher = new EventDispatcher();

        $entityChange = new EntityChangeSubscriber(
            $dispatcher,
            $this->getDeleteEnrichment()
        );

        $entityManager->getEventManager()->addEventSubscriber($entityChange);

        $this->buildServices($dispatcher, $entityManager);

        return $entityManager;
    }

    /**
     * Returns the container.
     *
     * @return \Illuminate\Container\Container
     */
    protected function getContainer(): Container
    {
        if ($this->container instanceof Container === false) {
            $this->createSchema();
        }

        return $this->container;
    }

    /**
     * Returns any delete enrichment required for a test.
     *
     * @return \EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface|null
     */
    protected function getDeleteEnrichment(): ?DeletedEntityEnrichmentInterface
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function getEntityPaths(): array
    {
        return [
            \implode(\DIRECTORY_SEPARATOR, [$this->getProjectPath(), 'tests', 'Integration', 'Fixtures', 'Entities'])
        ];
    }

    /**
     * Returns handlers to register in the EntityUpdateWorker.
     *
     * @phpstan-return array<class-string<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface>>
     *
     * @return string[]
     */
    protected function getRegisteredHandlers(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->container = null;
    }
}
