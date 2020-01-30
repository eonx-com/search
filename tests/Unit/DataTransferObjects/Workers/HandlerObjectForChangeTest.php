<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\DataTransferObjects\Workers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange
 */
final class HandlerObjectForChangeTest extends UnitTestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $update = new ObjectForUpdate(
            stdClass::class,
            ['property']
        );

        $handlerUpdate = new HandlerObjectForChange(
            'handler',
            $update
        );

        self::assertSame('handler', $handlerUpdate->getHandlerKey());
        self::assertSame($update, $handlerUpdate->getObjectForChange());
    }
}
