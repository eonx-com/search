<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
class PopulatorStub implements PopulatorInterface
{
    /**
     * Calls to the stub.
     *
     * @var mixed[]
     */
    private $calls = [];

    /**
     * @return mixed[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        int $batchSize
    ): void {
        $this->calls[__METHOD__][] = \compact('handler', 'indexSuffix', 'batchSize');
    }

    /**
     * {@inheritdoc}
     */
    public function populateWith(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        iterable $objects
    ): void {
        $this->calls[__METHOD__][] = \compact('handler', 'indexSuffix', 'objects');
    }
}
