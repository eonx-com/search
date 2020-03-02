<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\CustomAccessHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;

final class UpdateProcessor implements UpdateProcessorInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface
     */
    private $accessPopulator;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface
     */
    private $indexNameTransformer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    private $registeredHandlers;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface $accessPopulator
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $client
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface $indexNameTransformer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $registeredHandlers
     */
    public function __construct(
        AccessPopulatorInterface $accessPopulator,
        ClientInterface $client,
        IndexNameTransformerInterface $indexNameTransformer,
        RegisteredSearchHandlersInterface $registeredHandlers
    ) {
        $this->accessPopulator = $accessPopulator;
        $this->client = $client;
        $this->indexNameTransformer = $indexNameTransformer;
        $this->registeredHandlers = $registeredHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $indexSuffix, iterable $updates): void
    {
        $grouped = $this->groupUpdatesByHandler($updates);
        $actions = [];

        foreach ($grouped as $handlerKey => $changes) {
            $handler = $this->registeredHandlers->getTransformableHandlerByKey($handlerKey);
            $addAccess = $handler instanceof CustomAccessHandlerInterface === false;

            // Prefill any $changes with entities.
            $handler->prefill($changes);

            foreach ($changes as $change) {
                $documentAction = $handler->transform($change);

                // No action was generated.
                if ($documentAction instanceof DocumentAction === false) {
                    continue;
                }

                // If we got an update and the handler doesnt handle its own access management
                // we add an additional key for access tokens.
                if ($addAccess === true &&
                    $change instanceof ObjectForUpdate === true &&
                    $documentAction instanceof DocumentUpdate === true) {
                    $tokens = $this->accessPopulator->getAccessTokens($change);

                    $documentAction->addExtra(AccessTokenMappingHelper::ACCESS_TOKEN_PROPERTY, $tokens);
                }

                // Build the appropriate index name for the actions to occur in.
                $index = $this->indexNameTransformer->transformIndexName(
                    $handler,
                    $change
                ) . $indexSuffix;

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
     * @param iterable|\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     *
     * @phpstan-return array<string, array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<mixed>>>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[][]
     */
    private function groupUpdatesByHandler(iterable $updates): array
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
