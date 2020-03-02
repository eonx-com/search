<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Exceptions;

use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Exceptions\BulkFailureException
 */
final class BulkFailureExceptionTest extends UnitTestCase
{
    /**
     * Test exception returns errors from constructor.
     *
     * @return void
     */
    public function testExceptionErrors(): void
    {
        $exception = new BulkFailureException(['errors' => true]);

        self::assertSame(['errors' => true], $exception->getErrors());
    }
}
