<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\Search\Bridge\Laravel\Transformers\ProviderIndexTransformer;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;

final class ProviderSearchServiceProvider extends ServiceProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation is empty
     *
     * @inheritdoc
     */
    public function register(): void
    {
        $this->app->singleton(IndexTransformerInterface::class, ProviderIndexTransformer::class);
    }
}
