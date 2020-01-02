<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;

/**
 * @coversNothing
 */
final class UpdateProcessorStub extends BaseStub implements UpdateProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(string $indexSuffix, array $updates): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * Returns calls to process().
     *
     * @return mixed[]
     */
    public function getProcessCalls(): array
    {
        return $this->getCalls(__FUNCTION__);
    }
}
