<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;

final class Manager implements ManagerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\HandlerInterface[]
     */
    private $handlers;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface[] $handlers Handlers for data manipulation
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client Client instance to send update requests to
     */
    public function __construct(array $handlers, ClientInterface $client)
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

        foreach ($this->handlers as $handler) {
            if ($handler->handles($class) === false) {
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
        foreach ($this->handlers as $handler) {
            if ($handler->handles($class) === false) {
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

            $this->client->bulkUpdate($handler->getIndexName(), $transformed);
        }
    }

    /**
     * @inheritdoc
     */
    public function isSearchable(string $class): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler->handles($class)) {
                return true;
            }
        }

        return false;
    }
}
