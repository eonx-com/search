<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\DocumentAction
 */
final class DocumentActionTest extends TestCase
{
    /**
     * Tests DTO methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $update = new DocumentUpdate('id', 'document');

        self::assertSame('id', $update->getDocumentId());
    }
}
