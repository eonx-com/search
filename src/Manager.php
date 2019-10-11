<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;

final class Manager implements ManagerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface
     */
    private $handlers;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface
     */
    private $indexTransformer;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $handlers Search Handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client Client instance to send update requests to
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface $indexTransformer Index transformer
     */
    public function __construct(
        RegisteredSearchHandlerInterface $handlers,
        ClientInterface $client,
        IndexTransformerInterface $indexTransformer
    ) {
        $this->handlers = $handlers;
        $this->client = $client;
        $this->indexTransformer = $indexTransformer;
    }

    /**
     * @inheritdoc
     */
    public function getSearchMeta(object $object): array
    {
        $class = \get_class($object);

        $ids = [];

        foreach ($this->handlers->getEntityHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === false) {
                continue;
            }

            $searchId = $handler->getSearchId($object);

            // Make sure search id is provided/generated
            if ($searchId === null) {
                continue;
            }

            $indexName = $this->indexTransformer->transformIndexName($handler, $object);
            // Elastic search works with string ids, so we're forcing
            // them to strings here
            $ids[$indexName] = (string)$searchId;
        }

        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function handleDeletes(array $ids): void
    {
        $this->client->bulkDelete($ids);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdates(string $class, string $indexSuffix, array $objects): void
    {
        foreach ($this->handlers->getEntityHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === false) {
                continue;
            }

            $transformed = [];

            foreach ($objects as $object) {
                $searchId = $handler->getSearchId($object);

                if ($searchId === null) {
                    // the handler didnt generate a search id
                    continue;
                }

                $document = $handler->transform($object);

                if ($document === null) {
                    // no search document was generated

                    continue;
                }

                // elasticsearch works with string ids, so we're forcing
                // them to strings here
                $transformed[(string)$searchId] = $document;

                $indexName = $this->indexTransformer->transformIndexName($handler, $object);
                $index = \sprintf('%s%s', $indexName, $indexSuffix);

                $this->client->bulkUpdate($index, $transformed);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function isSearchable(string $class): bool
    {
        foreach ($this->handlers->getEntityHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if object is supported (handled) by the given search handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface $handler
     * @param string $class
     *
     * @return bool
     */
    private function isHandled(EntitySearchHandlerInterface $handler, string $class): bool
    {
        $implements = \class_implements($class);
        $implements[] = $class;

        $handles = $handler->getHandledClasses();
        $intersect = \array_intersect($implements, $handles);

        // If the supplied $class has any intersection of $implements, the handler
        // handles this class.
        return \count($intersect) > 0;
    }
}
