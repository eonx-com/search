<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
class TransformableHandlerStub implements TransformableSearchHandlerInterface
{
    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\DocumentAction[]
     */
    private $actions;

    /**
     * @phpstan-var array<class-string>
     *
     * @var string[]
     */
    private $handledClasses;

    /**
     * @var string|null
     */
    private $handlerKey;

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
     * @phpstan-param array<class-string> $handledClasses
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\DocumentAction[]|null $actions
     * @param mixed[]|null $handledClasses
     * @param null|string $handlerKey
     * @param string|null $indexName
     * @param mixed[]|null $objects
     */
    public function __construct(
        ?array $actions = null,
        ?array $handledClasses = null,
        ?string $handlerKey = null,
        ?string $indexName = null,
        ?array $objects = null
    ) {
        $this->actions = $actions ?? [];
        $this->handlerKey = $handlerKey;
        $this->handledClasses = $handledClasses ?? [];
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
        return $this->handledClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerKey(): string
    {
        return $this->handlerKey ?? 'transformable';
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
        return \array_shift($this->actions);
    }
}
