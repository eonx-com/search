<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects\Populators;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Populators\HandlerObjectForUpdate;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCase;

class HandlerObjectForUpdateTest extends TestCase
{
    /**
     * Tests the methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $forUpdate = new ObjectForUpdate(stdClass::class, ['id' => 1]);

        $handlerObjectUpdate = new HandlerObjectForUpdate(
            'handler',
            $forUpdate
        );

        self::assertSame('handler', $handlerObjectUpdate->getHandlerKey());
        self::assertSame($forUpdate, $handlerObjectUpdate->getObjectForUpdate());
    }
}
