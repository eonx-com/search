<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Elasticsearch\ClientBuilder;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
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
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required to ensure all services are bound
 */
final class SearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Builds all services against the container.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return void
     */
    public static function registerInContainer(Container $container): void
    {
        $container->singleton('search_elasticsearch_client', static function (Container $app) {
            return ClientBuilder::create()
                ->setConnectionParams([
                    'client' => [
                        'connect_timeout' => (int)\env('ELASTICSEARCH_CONNECTION_TIMEOUT', 2),
                        'timeout' => (int)\env('ELASTICSEARCH_TIMEOUT', 12),
                    ],
                ])
                ->setLogger($app->get(LoggerInterface::class))
                ->setHosts(\array_filter([(string)\env('ELASTICSEARCH_HOST', '')]))
                ->setSSLVerification((bool)\env('ELASTICSEARCH_VERIFY_SSL', true))
                ->build();
        });

        $container->singleton(AccessPopulatorInterface::class, AnonymousAccessPopulator::class);
        $container->singleton(ClientInterface::class, static function (Container $app): ClientInterface {
            return new Client(
                $app->get('search_elasticsearch_client'),
                $app->get(ClientBulkResponseHelperInterface::class)
            );
        });
        $container->singleton(ClientBulkResponseHelperInterface::class, ClientBulkResponseHelper::class);
        $container->singleton(IndexNameTransformerInterface::class, DefaultIndexNameTransformer::class);
        $container->singleton(IndexerInterface::class, Indexer::class);
        $container->singleton(MappingHelperInterface::class, AccessTokenMappingHelper::class);
        $container->singleton(PopulatorInterface::class, Populator::class);
        $container->singleton(RegisteredSearchHandlersInterface::class, static function (Container $app) {
            $searchHandlers = [];
            foreach ($app->tagged('search_handler') as $searchHandler) {
                /** @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface|mixed $searchHandler */
                if (($searchHandler instanceof SearchHandlerInterface) === false) {
                    continue;
                }

                $searchHandlers[] = $searchHandler;
            }

            return new RegisteredSearchHandlers($searchHandlers);
        });
        $container->singleton(
            RequestProxyFactoryInterface::class,
            static function (): RequestProxyFactory {
                return new RequestProxyFactory(
                    (string)\env('ELASTICSEARCH_HOST', 'https://admin:admin@elasticsearch:9200')
                );
            }
        );
        $container->singleton(ResponseFactoryInterface::class, ResponseFactory::class);
        $container->singleton(UpdateProcessorInterface::class, UpdateProcessor::class);

        // Bind workers
        $container->singleton(EntityUpdateWorkerInterface::class, static function (Container $app) {
            return new EntityUpdateWorker(
                $app->get(RegisteredSearchHandlersInterface::class),
                $app->get(EventDispatcherInterface::class),
                (int)\env('ELASTICSEARCH_UPDATES_BATCH_SIZE', 100)
            );
        });
    }
    
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation is empty
     *
     * {@inheritdoc}
     */
    public function register(): void
    {
        self::registerInContainer($this->app);
    }
}
