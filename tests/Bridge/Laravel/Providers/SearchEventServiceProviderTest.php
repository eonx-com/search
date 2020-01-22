<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
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
                EntityUpdateListener::class,
            ],
        ];

        self::assertSame($listen, $serviceProvider->listens());
    }
}
