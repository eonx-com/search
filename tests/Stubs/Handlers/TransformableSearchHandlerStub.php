<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;

/**
 * @coversNothing
 */
final class TransformableSearchHandlerStub implements TransformableSearchHandlerInterface
{
    /**
     * @var string
     */
    private $indexName;

    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\DocumentAction[]
     */
    private $objects;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\DocumentAction[]|null $objects
     * @param string|null $indexName
     */
    public function __construct(?array $objects = null, ?string $indexName = null)
    {
        $this->indexName = $indexName ?? 'valid';
        $this->objects = $objects ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [
            'doc' => [
                'dynamic' => 'strict',
                'properties' => [
                    'createdAt' => [
                        'type' => 'date',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [
            'number_of_replicas' => 1,
            'number_of_shards' => 1,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        return $this->objects ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledClasses(): array
    {
        return [SearchableStub::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object = null): ?DocumentAction
    {
        return \array_shift($this->objects);
    }
}
