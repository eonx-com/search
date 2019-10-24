<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteDataListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchEventServiceProvider;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchEventServiceProvider
 */
final class SearchEventServiceProviderTest extends TestCase
{
    /**
     * Test listens.
     *
     * @return void
     */
    public function testListens(): void
    {
        $application = $this->createApplication();

        $serviceProvider = new SearchEventServiceProvider($application);

        $listen = [
            EntityChangeEvent::class => [
                EntityDeleteListener::class,
                EntityUpdateListener::class
            ],
            EntityDeleteDataEvent::class => [
                EntityDeleteDataListener::class
            ],
        ];

        static::assertSame($listen, $serviceProvider->listens());
    }
}
