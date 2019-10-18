<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Laravel\Lumen\Providers\EventServiceProvider;
use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteDataListener;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteListener;
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
            EntityDeleteDataEvent::class => [
                EntityDeleteDataListener::class
            ],
            EntityChangeEvent::class => [
                EntityDeleteListener::class,
                EntityUpdateListener::class
            ]
        ];

        parent::__construct($app);
    }
}
