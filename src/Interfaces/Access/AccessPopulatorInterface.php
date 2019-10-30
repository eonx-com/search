<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Access;

interface AccessPopulatorInterface
{
    /**
     * Any implementation of this service should return the access tokens
     * to associate with the object that is passed. When passed, the object
     * is being transformed into a search document and should have any
     * access tokens embedded in that document for access control.
     *
     * @param object $object
     *
     * @return string[]
     */
    public function getAccessTokens(object $object): array;
}
