<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
class TransformableHandlerStub extends BaseStub implements TransformableSearchHandlerInterface
{
    /**
     * @var string
     */
    private $indexName;

    /**
     * Constructor.
     *
     * @param string|null $indexName
     * @param mixed[]|null $responses
     */
    public function __construct(?string $indexName = null, ?array $responses = null)
    {
        parent::__construct($responses);

        $this->indexName = $indexName ?? 'valid';
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
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerKey(): string
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
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
    public function getSubscriptions(): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ObjectForChange $object): ?DocumentAction
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());

        return $this->returnOrThrowResponse(__FUNCTION__);
    }
}
