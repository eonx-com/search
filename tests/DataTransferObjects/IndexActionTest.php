<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\IndexAction
 */
final class IndexActionTest extends TestCase
{
    /**
     * Tests DTO methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $documentAction = new DocumentUpdate('id', 'document');
        $action = new IndexAction($documentAction, 'index');

        self::assertSame($documentAction, $action->getDocumentAction());
        self::assertSame('index', $action->getIndex());
    }
}
