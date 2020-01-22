<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Handlers;

/**
 * This is a DTO used by the search system to keep track of objects (and their ids) that should be
 * updated and which handler will receive the DTO.
 *
 * The object is serialised into an async queue message to be actioned by a worker process.
 */
final class ObjectForUpdate extends ObjectForChange
{
}
