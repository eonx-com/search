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
     * This method returns data about the deleted objects that should
     * be removed from indices.
     *
     * @param object[] $deletes
     *
     * @return string[][][]
     */
    public function handle(array $deletes): array
    {
        $ids = [];

        foreach ($deletes as $entity) {
            $searchIds = $this->searchManager->getSearchMeta($entity);

            foreach ($searchIds as $index => $searchId) {
                if (\array_key_exists($index, $ids) === false) {
                    $ids[(string)$index] = [];
                }

                $ids[(string)$index][] = $searchId;
            }
        }

        return [
            'search' => $ids,
        ];
    }
}
