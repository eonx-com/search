<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Indexer;

final class IndexCleanResult
{
    /**
     * @var string[]
     */
    private $indices;

    /**
     * IndexCleanResult constructor.
     *
     * @param string[] $indices
     */
    public function __construct(array $indices)
    {
        $this->indices = $indices;
    }

    /**
     * Get indices that will be cleaned
     *
     * @return string[]
     */
    public function getIndicesCleaned(): array
    {
        return $this->indices;
    }
}
