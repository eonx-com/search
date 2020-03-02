<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Providers;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\BatchOfUpdatesListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchEventServiceProvider;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchEventServiceProvider
 */
final class SearchEventServiceProviderTest extends UnitTestCase
{
    /**
     * Test listens.
     *
     * @return void
     */
    public function testListens(): void
    {
        $application = new ApplicationStub();

        $serviceProvider = new SearchEventServiceProvider($application);

        $listen = [
            EntityChangeEvent::class => [
                EntityUpdateListener::class,
            ],
            BatchOfUpdatesEvent::class => [
                BatchOfUpdatesListener::class,
            ],
        ];

        self::assertSame($listen, $serviceProvider->listens());
    }
}
