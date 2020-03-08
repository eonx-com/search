<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Factories;

use LoyaltyCorp\Search\Bridge\Symfony\Factories\RegisteredSearchHandlersFactory;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Factories\RegisteredSearchHandlersFactory
 */
final class RegisteredSearchHandlersFactoryTest extends UnitTestCase
{
    /**
     * Test create instance of RegisteredSearchHandlers with default value.
     */
    public function testCreateRegisteredSearchHandlersClient(): void
    {
        $expectedHandlers = [
            new NonDoctrineHandlerStub(),
            new TransformableHandlerStub()
        ];

        $registeredHandlers = (new RegisteredSearchHandlersFactory($this->toIterable($expectedHandlers)))->create();

        self::assertEquals($expectedHandlers, $this->getPrivatePropertyValue($registeredHandlers, 'searchHandlers'));
    }

    /**
     * Return dummy search handlers as iterable for `!tagged search_handler`
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $toYield
     *
     * @return iterable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface>
     */
    private function toIterable(array $toYield): iterable
    {
        foreach ($toYield as $searchHandler) {
            yield $searchHandler;
        }
    }
}
