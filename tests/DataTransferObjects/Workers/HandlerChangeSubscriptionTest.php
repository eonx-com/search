<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects\Workers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription
 */
final class HandlerChangeSubscriptionTest extends TestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $subscription = new ChangeSubscription(
            stdClass::class,
            ['property']
        );

        $handlerSubscription = new HandlerChangeSubscription(
            'handler',
            $subscription
        );

        self::assertSame('handler', $handlerSubscription->getHandlerKey());
        self::assertSame($subscription, $handlerSubscription->getSubscription());
    }
}
