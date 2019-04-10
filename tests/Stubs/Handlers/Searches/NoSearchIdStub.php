<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches;

final class NoSearchIdStub
{
    /**
     * Get search id for this stub
     *
     * @return string|null
     */
    public function getSearchId(): ?string
    {
        return null;
    }
}
