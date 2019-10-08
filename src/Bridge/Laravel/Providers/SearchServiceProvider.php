<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use Elasticsearch\ClientBuilder;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Helpers\IndexHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\IndexHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
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
            return new Client(
                ClientBuilder::create()
                    ->setLogger($app->make(LoggerInterface::class))
                    ->setHosts(\array_filter([(string)\env('ELASTICSEARCH_HOST', '')]))
                    ->setSSLVerification(false)
                    ->build(),
                $app->make(ClientBulkResponseHelperInterface::class)
            );
        });

        $this->app->singleton(IndexHelperInterface::class, IndexHelper::class);
        $this->app->singleton(IndexerInterface::class, Indexer::class);

        // Bind search manager
        $this->app->singleton(ManagerInterface::class, Manager::class);
        $this->app->singleton(ClientBulkResponseHelperInterface::class, ClientBulkResponseHelper::class);

        $this->app->singleton(EntityManagerHelperInterface::class, static function (Container $app) {
            /**
             * @var \Doctrine\Common\Persistence\ManagerRegistry|mixed $endpoint
             *
             * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises check
             */
            $registry = $app->get('registry');

            if (($registry instanceof ManagerRegistry) === true &&
                ($registry->getManager() instanceof DoctrineEntityManagerInterface) === true) {
                return new EntityManagerHelper(
                    $registry->getManager(),
                    $app->make(EoneoPayEntityManagerInterface::class)
                );
            }

            throw new BindingResolutionException('Could not resolve Entity Manager from application container');
        });

        $this->app->singleton(RegisteredSearchHandlerInterface::class, static function (Container $app) {
            $searchHandlers = [];
            foreach ($app->tagged('search_handler') as $searchHandler) {
                /** @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface|mixed $searchHandler */
                if (($searchHandler instanceof SearchHandlerInterface) === false) {
                    continue;
                }

                $searchHandlers[] = $searchHandler;
            }

            return new RegisteredSearchHandler($searchHandlers);
        });
    }
}
