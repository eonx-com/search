<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures;

use EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface;

class DeletedEntityIdEnrichment implements DeletedEntityEnrichmentInterface
{
    /**
     * Adds a deleted Id property to the metadata array.
     *
     * {@inheritdoc}
     */
    public function getMetadata(object $entity): array
    {
        if (\method_exists($entity, 'getId') === false) {
            return [];
        }

        return [
            'deletedId' => $entity->getId()
        ];
    }
}
