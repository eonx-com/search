<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Access;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

interface AccessPopulatorInterface
{
    /**
     * Any implementation of this service should return the access tokens
     * to associate with the object that is passed. When passed, the object
     * is being transformed into a search document and should have any
     * access tokens embedded in that document for access control.
     *
     * @phpstan-param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate<mixed> $object
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate $object
     *
     * @return string[]
     */
    public function getAccessTokens(ObjectForUpdate $object): array;
}
