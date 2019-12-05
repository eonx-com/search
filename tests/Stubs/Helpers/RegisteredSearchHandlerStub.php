<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

/**
 * @coversNothing
 */
final class RegisteredSearchHandlerStub implements RegisteredSearchHandlerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    private $searchHandlers;

    /**
     * RegisteredSearchHandlers constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $searchHandlers
     */
    public function __construct(array $searchHandlers)
    {
        $this->searchHandlers = $searchHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->searchHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlerByKey(string $key): TransformableSearchHandlerInterface
    {
        return new TransformableSearchHandlerStub();
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformableHandlers(): array
    {
        $entityHandlers = [];

        foreach ($this->searchHandlers as $handler) {
            if ($handler instanceof TransformableSearchHandlerInterface === true) {
                $entityHandlers[] = $handler;
            }
        }

        return $entityHandlers;
    }
}
