<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Access;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;

final class AnonymousAccessPopulator implements AccessPopulatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAccessTokens(ObjectForUpdate $object): array
    {
        // All documents are anonymous by default. Implement your own service
        // for AccessTransformerInterface to define access controls for the object.
        return ['anonymous'];
    }
}
