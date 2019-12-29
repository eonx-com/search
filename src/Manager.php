<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

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
     * @var \LoyaltyCorp\Search\Interfaces\PopulatorInterface
     */
    private $populator;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface $populator
     */
    public function __construct(
        RegisteredSearchHandlerInterface $handlers,
        ClientInterface $client,
        PopulatorInterface $populator
    ) {
        $this->handlers = $handlers;
        $this->client = $client;
        $this->populator = $populator;
    }

    /**
     * {@inheritdoc}
     */
    public function handleDeletes(array $ids): void
    {
        $actions = [];

        foreach ($ids as $index => $indexIds) {
            foreach ($indexIds as $documentId) {
                $actions[] = new IndexAction(new DocumentDelete($documentId), $index);
            }
        }

        $this->client->bulk($actions);
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdates(string $class, string $indexSuffix, array $objects): void
    {
        foreach ($this->handlers->getTransformableHandlers() as $handler) {
            if ($this->isHandled($handler, $class) === false) {
                continue;
            }

            $this->populator->populateWith($handler, $indexSuffix, $objects);
        }
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
