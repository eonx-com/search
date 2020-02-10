<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Access;

use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use stdClass;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Access\AnonymousAccessPopulator
 */
final class AnonymousAccessPopulatorTest extends UnitTestCase
{
    /**
     * Tests that the anonymous access populator returns an anonymous access
     * token.
     *
     * @return void
     */
    public function testAnonymous(): void
    {
        $populator = new AnonymousAccessPopulator();
        $expected = ['anonymous'];

        $tokens = $populator->getAccessTokens(new ObjectForUpdate(
            stdClass::class,
            []
        ));

        self::assertSame($expected, $tokens);
    }
}
