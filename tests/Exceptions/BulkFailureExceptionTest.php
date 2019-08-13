<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Exceptions;

use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Exceptions\BulkFailureException
 */
final class BulkFailureExceptionTest extends TestCase
{
    /**
     * Test exception returns errors from constructor
     *
     * @return void
     */
    public function testExceptionErrors(): void
    {
        $exception = new BulkFailureException(['errors' => true]);

        self::assertSame(['errors' => true], $exception->getErrors());
    }
}
