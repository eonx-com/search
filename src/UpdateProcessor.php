<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;

final class UpdateProcessor implements UpdateProcessorInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface
     */
    private $registeredHandlers;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     */
    public function __construct(
        ClientInterface $client,
        RegisteredSearchHandlerInterface $registeredHandlers
    ) {
        $this->client = $client;
        $this->registeredHandlers = $registeredHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $indexSuffix, array $updates): void
    {
        $grouped = $this->groupUpdatesByHandler($updates);
        $actions = [];

        foreach ($grouped as $handlerKey => $changes) {
            $handler = $this->registeredHandlers->getTransformableHandlerByKey($handlerKey);

            // Build the appropriate index name for the actions to occur in.
            $index = $handler->getIndexName() . $indexSuffix;

            foreach ($changes as $change) {
                $documentAction = $handler->transform($change);

                // No action was generated.
                if ($documentAction instanceof DocumentAction === false) {
                    continue;
                }

                // Wrap each DocumentAction in an IndexAction for the client.
                $actions[] = new IndexAction($documentAction, $index);
            }
        }

        // If we didnt build any actions, return.
        if (\count($actions) === 0) {
            return;
        }

        // Send the actions out as bulk.
        $this->client->bulk($actions);
    }

    /**
     * Groups the incoming HandlerObjectForChange DTOs into a multidimensional array of
     * ObjectForChange DTOs grouped by their handler keys.
     *
     * @phpstan-return array<string, array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange>>
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[][]
     */
    private function groupUpdatesByHandler(array $updates): array
    {
        $grouped = [];

        foreach ($updates as $update) {
            if (\array_key_exists($update->getHandlerKey(), $grouped) === false) {
                $grouped[$update->getHandlerKey()] = [];
            }

            $grouped[$update->getHandlerKey()][] = $update->getObjectForChange();
        }

        return $grouped;
    }
}
