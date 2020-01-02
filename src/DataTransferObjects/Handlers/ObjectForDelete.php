<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This DTO serves the same purpose as ObjectForUpdate, but indicates a deletion should occur.
 */
final class ObjectForDelete extends ObjectForChange
{
}
