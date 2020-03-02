<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Indexer;

final class IndexSwapResult
{
    /**
     * @var string[][]
     */
    private $movingAliases;

    /**
     * @var string[]
     */
    private $removingAliases;

    /**
     * @var string[]
     */
    private $skipIndices;

    /**
     * IndexSwapResult constructor.
     *
     * @param string[][] $aliasesToMove
     * @param string[] $aliasesToRemove
     * @param string[] $skipIndices
     */
    public function __construct(array $aliasesToMove, array $aliasesToRemove, array $skipIndices)
    {
        $this->movingAliases = $aliasesToMove;
        $this->removingAliases = $aliasesToRemove;
        $this->skipIndices = $skipIndices;
    }

    /**
     * Render an array of table structure results of actions that will be taken.
     *
     * @return mixed[]
     */
    public function getTableData(): array
    {
        $rows = [];
        $header = ['Alias', 'Index', 'Action'];

        foreach ($this->movingAliases as $action) {
            $rows[] = [$action['alias'], $action['index'], 'Point alias to index'];
        }

        foreach ($this->removingAliases as $action) {
            $rows[] = [$action, '', 'Remove alias'];
        }

        foreach ($this->skipIndices as $action) {
            $rows[] = ['', $action, 'Skip swapping root alias'];
        }

        return [
            $header,
            $rows,
        ];
    }
}
