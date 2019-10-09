<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use LoyaltyCorp\Search\Bridge\Laravel\Providers\ProviderSearchServiceProvider;
use LoyaltyCorp\Search\Bridge\Laravel\Transformers\ProviderIndexTransformer;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\ProviderSearchServiceProvider
 */
final class ProviderSearchServiceProviderTest extends TestCase
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
        (new ProviderSearchServiceProvider($application))->register();

        // Ensure services are bound
        self::assertInstanceOf(
            ProviderIndexTransformer::class,
            $application->make(IndexTransformerInterface::class)
        );
    }
}
