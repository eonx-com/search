<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Access;

use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;

class AnonymousAccessPopulator implements AccessPopulatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAccessTokens(object $object): array
    {
        // All documents are anonymous by default. Implement your own service
        // for AccessTransformerInterface to define access controls for the object.
        $transformed[AccessTokenMappingHelper::ACCESS_TOKEN_PROPERTY] = ['anonymous'];

        return $transformed;
    }
}
