<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\DataTransferObjects\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete
 */
final class ObjectForDeleteTest extends UnitTestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $subscription = new ObjectForDelete(
            stdClass::class,
            ['id'],
            ['meta' => 'data']
        );

        self::assertSame(stdClass::class, $subscription->getClass());
        self::assertSame(['id'], $subscription->getIds());
        self::assertSame(['meta' => 'data'], $subscription->getMetadata());
    }
}
