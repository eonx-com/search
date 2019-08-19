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
     * @var mixed[]
     */
    private $transformed;

    /**
     * NonDoctrineHandlerStub constructor.
     *
     * @param mixed[] $transformed
     */
    public function __construct(?array $transformed = null)
    {
        $this->transformed = $transformed ?? [];
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
    public function getIndexName(): string
    {
        return 'non-doctrine-index';
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object = null): ?array
    {
        return $this->transformed;
    }
}
