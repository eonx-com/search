<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformerInterface;

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
     * @var \LoyaltyCorp\Search\Interfaces\TransformerInterface
     */
    private $transformer;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $handlers Search Handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client Client instance to send update requests to
     * @param \LoyaltyCorp\Search\Interfaces\TransformerInterface $transformer
     */
    public function __construct(
        RegisteredSearchHandlerInterface $handlers,
        ClientInterface $client,
        TransformerInterface $transformer
    ) {
        $this->handlers = $handlers;
        $this->client = $client;
        $this->transformer = $transformer;
    }

    /**
     * @inheritdoc
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

            // Elastic search works with string ids, so we're forcing
            // them to strings here
            $ids[$handler->getIndexName()] = (string)$searchId;
        }

        return $ids;
    }

    /**
     * Determine if object is supported (handled) by the given search handler
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
        foreach ($this->handlers->getTransformableHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === false) {
                continue;
            }

            $this->handleUpdatesWithHandler($handler, $indexSuffix, $objects);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdatesWithHandler(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        array $objects
    ): void {
        /**
         * Required because iterator_to_array clobbers the type returned by the transformer.
         *
         * @var mixed[][] $transformed
         */
        $transformed = \iterator_to_array($this->transformer->bulkTransform($handler, $objects));

        if (\count($transformed) === 0) {
            // there were no transformed documents created by the handler, we have
            // nothing to update
            return;
        }

        $index = \sprintf('%s%s', $handler->getIndexName(), $indexSuffix);

        $this->client->bulkUpdate($index, $transformed);
    }
}
