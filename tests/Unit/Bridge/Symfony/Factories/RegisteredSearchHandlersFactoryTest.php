<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Factories;

use LoyaltyCorp\Search\Bridge\Symfony\Factories\RegisteredSearchHandlersFactory;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers;
use ReflectionProperty;
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
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreateRegisteredSearchHandlersClient(): void
    {
        $expectedHandlers = [
            new NonDoctrineHandlerStub(),
            new TransformableHandlerStub()
        ];

        $registeredHandlers = (new RegisteredSearchHandlersFactory($this->toIterable($expectedHandlers)))->create();

        // Use reflection to assert private properties are set properly.
        $actualSearchHandlers = new ReflectionProperty(RegisteredSearchHandlers::class, 'searchHandlers');
        $actualSearchHandlers->setAccessible(true);

        self::assertEquals($expectedHandlers, $actualSearchHandlers->getValue($registeredHandlers));
    }

    /**
     * Return dummy search handlers as iterable for `!tagged search_handler`
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $toYield
     *
     * @return \Traversable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface>
     */
    private function toIterable(array $toYield): \Traversable
    {
        foreach ($toYield as $searchHandler) {
            yield $searchHandler;
        }
    }
}
