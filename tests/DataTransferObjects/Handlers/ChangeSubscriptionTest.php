<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects\Handlers;

use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription
 *
 * @SuppressWarnings(PHPMD)
 */
final class ChangeSubscriptionTest extends TestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        /**
         * A function that matches the required PHPStan signature for $transform.
         *
         * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $update
         *
         * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
         */
        $func = static function (ChangedEntity $update): array {
            return [];
        };

        $subscription = new ChangeSubscription(
            stdClass::class,
            ['property'],
            $func
        );

        self::assertSame(stdClass::class, $subscription->getClass());
        self::assertSame(['property'], $subscription->getProperties());
        self::assertSame($func, $subscription->getTransform());
    }
}
