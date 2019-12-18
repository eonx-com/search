<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;

/**
 * @coversNothing
 */
final class OtherTransformableSearchHandlerStub implements TransformableSearchHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHandlerKey(): string
    {
        return '';
    }

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
     * {@inheritdoc}
     */
    public function getSubscriptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'valid2';
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchId(object $object)
    {
        return \method_exists($object, 'getSearchId') ? $object->getSearchId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object = null): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
