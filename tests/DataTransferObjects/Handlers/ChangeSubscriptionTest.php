<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription
 */
class ChangeSubscriptionTest extends TestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        /**
         * @param \LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated $update
         *
         * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate[]
         */
        $func = static function (ObjectUpdated $update): array {
            return [];
        };

        $subscription = new ChangeSubscription(
            'class',
            ['property'],
            $func
        );

        self::assertSame('class', $subscription->getClass());
        self::assertSame(['property'], $subscription->getProperties());
        self::assertSame($func, $subscription->getTransform());
    }
}
