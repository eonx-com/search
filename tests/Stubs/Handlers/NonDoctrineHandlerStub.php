<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\SearchInterface;

/**
 * @coversNothing
 */
class NonDoctrineHandlerStub implements SearchInterface
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
    public function getIndexName(): string
    {
        return 'non-doctrine-index';
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object = null): ?array
    {
        return [];
    }
}