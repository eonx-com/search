<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;

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
     * Constructor
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $handlers Search Handlers
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client Client instance to send update requests to
     */
    public function __construct(RegisteredSearchHandlerInterface $handlers, ClientInterface $client)
    {
        $this->handlers = $handlers;
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function getSearchMeta(object $object): array
    {
        $class = \get_class($object);

        $ids = [];

        foreach ($this->handlers->getAll() as $handler) {
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
     * @inheritdoc
     */
    public function handleDeletes(array $ids): void
    {
        $this->client->bulkDelete($ids);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdates(string $class, array $objects): void
    {
        foreach ($this->handlers->getAll() as $handler) {
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
            }

            if (\count($transformed) === 0) {
                // there were no transformed documents created by the handler, we have
                // nothing to update
                continue;
            }

            $this->client->bulkUpdate($handler->getIndexName(), $transformed);
        }
    }

    /**
     * @inheritdoc
     */
    public function isSearchable(string $class): bool
    {
        foreach ($this->handlers->getAll() as $handler) {
            if ($this->isHandled($handler, $class) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if object is supported (handled) by the given search handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $handler
     * @param string $class
     *
     * @return bool
     */
    private function isHandled(HandlerInterface $handler, string $class): bool
    {
        return \in_array($class, $handler->getHandledClasses(), true);
    }
}
