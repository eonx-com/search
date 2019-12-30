<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Exceptions\DuplicateSearchHandlerKeyException;
use LoyaltyCorp\Search\Exceptions\HandlerDoesntExistException;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

final class RegisteredSearchHandler implements RegisteredSearchHandlerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface[]|null
     */
    private $handlersByKey;

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
        if ($this->handlersByKey === null) {
            $this->handlersByKey = $this->buildHandlersByKey();
        }

        $handler = $this->handlersByKey[$key] ?? null;

        if ($handler instanceof TransformableSearchHandlerInterface === false) {
            throw new HandlerDoesntExistException(\sprintf(
                'The handler with key "%s" does not exist.',
                $key
            ));
        }

        return $handler;
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

    /**
     * Builds the search array for handlers by key.
     *
     * @return \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface[]
     *
     * @throws \LoyaltyCorp\Search\Exceptions\DuplicateSearchHandlerKeyException
     */
    private function buildHandlersByKey(): array
    {
        $handlersByKey = [];

        foreach ($this->getTransformableHandlers() as $handler) {
            if (\array_key_exists($handler->getHandlerKey(), $handlersByKey) === true) {
                throw new DuplicateSearchHandlerKeyException(\sprintf(
                    'The handler key "%s" is duplicated and must be unique.',
                    $handler->getHandlerKey()
                ));
            }

            $handlersByKey[$handler->getHandlerKey()] = $handler;
        }

        return $handlersByKey;
    }
}
