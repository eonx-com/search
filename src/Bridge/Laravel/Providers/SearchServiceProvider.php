<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Elasticsearch\ClientBuilder;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Manager;

final class SearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation returns empty array
     *
     * @inheritdoc
     */
    public function provides(): array
    {
        return [ClientInterface::class, ManagerInterface::class];
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

        // Bind search manager
        $this->app->singleton(ManagerInterface::class, static function (Container $app): ManagerInterface {
            // Get handlers tagged in app
            $handlers = [];
            \array_push($handlers, ...$app->tagged('search_handler'));

            // Filter handlers to only include handlers implementing the correct interface
            $handlers = \array_filter($handlers, static function ($handler): bool {
                return $handler instanceof HandlerInterface;
            });

            // Create manager
            return new Manager(
                $handlers,
                $app->make(ClientInterface::class)
            );
        });
    }
}
