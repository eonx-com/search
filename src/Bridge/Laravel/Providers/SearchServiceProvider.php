<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Elasticsearch\ClientBuilder;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Manager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required to ensure all services are bound
 */
final class SearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation returns empty array
     *
     * @inheritdoc
     */
    public function provides(): array
    {
        return [
            ClientInterface::class,
            EntityManagerHelperInterface::class,
            IndexerInterface::class,
            ManagerInterface::class,
            RegisteredSearchHandlerInterface::class
        ];
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation is empty
     *
     * @inheritdoc
     */
    public function register(): void
    {
        // Bind elasticsearch client
        $this->app->singleton(ClientInterface::class, static function (Container $app): ClientInterface {
            return new Client(ClientBuilder::create()
                ->setLogger($app->make(LoggerInterface::class))
                ->setHosts(\array_filter([(string)\env('ELASTICSEARCH_HOST', '')]))
                ->setSSLVerification(false)
                ->build());
        });

        $this->app->singleton(IndexerInterface::class, Indexer::class);

        // Bind search manager
        $this->app->singleton(ManagerInterface::class, Manager::class);
        $this->app->singleton(EntityManagerHelperInterface::class, static function (Container $app) {
            return new EntityManagerHelper($app);
        });

        $this->app->singleton(RegisteredSearchHandlerInterface::class, static function (Container $app) {
            $searchHandlers = [];
            foreach ($app->tagged('search_handler') as $searchHandler) {
                /** @var \LoyaltyCorp\Search\Interfaces\HandlerInterface|mixed $searchHandler */
                if (($searchHandler instanceof HandlerInterface) === false) {
                    continue;
                }

                $searchHandlers[] = $searchHandler;
            }

            return new RegisteredSearchHandler($searchHandlers);
        });
    }
}
