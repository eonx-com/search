<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;

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
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface
     */
    private $nameTransformer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\PopulatorInterface
     */
    private $populator;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface $nameTransformer
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface $populator
     */
    public function __construct(
        RegisteredSearchHandlerInterface $handlers,
        ClientInterface $client,
        IndexNameTransformerInterface $nameTransformer,
        PopulatorInterface $populator
    ) {
        $this->handlers = $handlers;
        $this->client = $client;
        $this->nameTransformer = $nameTransformer;
        $this->populator = $populator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchMeta(object $object): array
    {
        $class = \get_class($object);

        $ids = [];

        foreach ($this->handlers->getTransformableHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === false) {
                continue;
            }

            $searchId = $handler->getSearchId($object);

            // Make sure search id is provided/generated
            if ($searchId === null) {
                continue;
            }

            $indexName = $this->nameTransformer->transformIndexName($handler, $object);
            // Elastic search works with string ids, so we're forcing
            // them to strings here
            $ids[$indexName] = (string)$searchId;
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function handleDeletes(array $ids): void
    {
        $this->client->bulkDelete($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdates(string $indexSuffix, array $objects): void
    {
        // TBD. Iterate over objects and call handlers that want to know about it.
    }

    /**
     * Determine if object is supported (handled) by the given search handler.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param string $class
     *
     * @return bool
     */
    private function isHandled(TransformableSearchHandlerInterface $handler, string $class): bool
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
