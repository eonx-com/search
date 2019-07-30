<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch\Namespaces;

use Elasticsearch\Namespaces\IndicesNamespace as BaseIndicesNamespace;

/**
 * @coversNothing
 */
class IndicesNamespaceStub extends BaseIndicesNamespace
{
    /**
     * {@inheritdoc}
     */
    public function updateAliases($params = [])
    {
        return [];
    }
}
