<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use Laravel\Lumen\Providers\EventServiceProvider;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;

final class SearchEventServiceProvider extends EventServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($app)
    {
        // Set listeners
        $this->listen = [
            EntityChangeEvent::class => [
                EntityUpdateListener::class,
            ],
        ];

        parent::__construct($app);
    }
}
