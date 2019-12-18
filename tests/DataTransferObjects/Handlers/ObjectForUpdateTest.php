<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\ObjectUpdated;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate
 */
class ObjectForUpdateTest extends TestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $subscription = new ObjectForUpdate(
            'class',
            'handler',
            ['id']
        );

        self::assertSame('class', $subscription->getClass());
        self::assertSame('handler', $subscription->getHandlerKey());
        self::assertSame(['id'], $subscription->getIds());
    }
}
