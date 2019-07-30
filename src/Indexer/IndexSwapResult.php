<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Indexer;

final class IndexSwapResult
{
    /**
     * @var string[][]
     */
    protected $movingAliases = [];

    /**
     * @var string[]
     */
    protected $removingAliases = [];

    /**
     * IndexSwapResult constructor.
     *
     * @param string[][] $aliasesToMove
     * @param string[] $aliasesToRemove
     */
    public function __construct(array $aliasesToMove, array $aliasesToRemove)
    {
        $this->movingAliases = $aliasesToMove;
        $this->removingAliases = $aliasesToRemove;
    }

    /**
     * Render an array of table structure results of actions that will be taken
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

        return [
            $header,
            $rows
        ];
    }
}
