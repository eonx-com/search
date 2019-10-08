<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;


use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Helpers\ProviderAwareRegisteredSearchHandler;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

final class ProviderSearchServiceProvider extends ServiceProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation is empty
     *
     * @inheritdoc
     */
    public function register(): void
    {
        $this->app->singleton(RegisteredSearchHandlerInterface::class, static function (Container $app) {
            $searchHandlers = [];
            foreach ($app->tagged('search_handler') as $searchHandler) {
                /** @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface|mixed $searchHandler */
                if (($searchHandler instanceof SearchHandlerInterface) === false) {
                    continue;
                }

                $searchHandlers[] = $searchHandler;
            }

            $providerIds = [];
            foreach ($app->tagged('provider_ids') as $providerId) {
                $providerIds[] = $providerId;
            }

            return new ProviderAwareRegisteredSearchHandler(
                $searchHandlers,
                $app->make(EoneoPayEntityManagerInterface::class)
            );
        });
    }
}
