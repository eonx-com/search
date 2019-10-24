<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

/**
 * @coversNothing
 */
class NonDoctrineHandlerStub implements SearchHandlerInterface
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
}
