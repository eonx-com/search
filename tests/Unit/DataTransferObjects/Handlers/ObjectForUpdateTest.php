<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\DataTransferObjects\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate
 */
final class ObjectForUpdateTest extends UnitTestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $subscription = new ObjectForUpdate(
            stdClass::class,
            ['id']
        );

        $object = new stdClass();
        $subscription->setObject($object);

        self::assertSame(stdClass::class, $subscription->getClass());
        self::assertSame(['id'], $subscription->getIds());
        self::assertSame($object, $subscription->getObject());
    }
}
