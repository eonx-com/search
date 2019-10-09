<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Transformers;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Multitenancy\Database\Entities\Provider;
use LoyaltyCorp\Search\Bridge\Laravel\Transformers\ProviderIndexTransformer;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\ProviderAwareSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Transformers\ProviderIndexTransformer
 */
class ProviderIndexTransformerTest extends TestCase
{
    /**
     * Test that transform index names with certain provider will return expected
     * array of index names.
     *
     * @return void
     */
    public function testTransformIndexNames(): void
    {
        $provider = new Provider('providerId', 'Acme Inc');
        $handler = new ProviderAwareSearchHandlerStub();
        $entityManager = new EntityManagerStub([$provider]);
        $transformer = $this->getTransformer($entityManager);

        $expectedIndexNames = [
            'provider-aware-index_providerId'
        ];

        $actualIndexNames = $transformer->transformIndexNames($handler);

        self::assertSame($expectedIndexNames, $actualIndexNames);
    }

    /**
     * Get provider index transformer.
     *
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Transformers\ProviderIndexTransformer
     */
    private function getTransformer(EntityManagerInterface $entityManager): ProviderIndexTransformer
    {
        return new ProviderIndexTransformer($entityManager);
    }
}
