<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Workers;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

class EntityUpdateWorkerStub extends BaseStub implements EntityUpdateWorkerInterface
{
    /**
     * Returns calls to handle().
     *
     * @return mixed[]
     */
    public function getHandleCalls(): array
    {
        return $this->getCalls(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $changes): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }
}
