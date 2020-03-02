<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

/**
 * This interface is used to signal to the search package that the
 * search handler wants to opt out of access controls. Implementing
 * this interface means that the _access key is not added to mapping
 * and that when populating the index, no access tokens are generated
 * for a document.
 */
interface CustomAccessHandlerInterface
{
}
