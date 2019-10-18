<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\Interfaces\ManagerInterface;

final class EntityDeleteDataWorker
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $searchManager;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $searchManager
     */
    public function __construct(ManagerInterface $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    /**
     * TODO Define this title
     *
     * @param object[] $deletes
     *
     * @return mixed[]
     */
    public function handle(array $deletes): array
    {
        $ids = [];

        foreach ($deletes as $entity) {
            $searchIds = $this->searchManager->getSearchMeta($entity);

            foreach ($searchIds as $index => $searchId) {
                if (\array_key_exists($index, $ids) === false) {
                    $ids[$index] = [];
                }

                $ids[$index][] = $searchId;
            }
        }

        return [
            'search' => $ids
        ];
    }
}
