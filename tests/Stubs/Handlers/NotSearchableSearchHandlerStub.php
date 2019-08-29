<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;

/**
 * @coversNothing
 */
final class NotSearchableSearchHandlerStub implements TransformableSearchHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getHandledClasses(): array
    {
        return [NotSearchableStub::class];
    }

    /**
     * @inheritdoc
     */
    public function getIndexName(): string
    {
        return 'valid';
    }

    /**
     * @inheritdoc
     */
    public function getSearchId(object $object)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function transform($object = null): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
