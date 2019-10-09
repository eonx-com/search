<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Transformers;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Multitenancy\Database\Entities\Provider;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;

final class ProviderIndexTransformer implements IndexTransformerInterface
{
    /**
     * @var \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface
     */
    private $entityManager;

    /**
     * ProviderAwareRegisteredSearchHandler constructor.
     *
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager Entity manager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array
    {
        $indexNames = [];
        $providerIds = $this->fetchAllProviderIds();

        foreach ($providerIds as $providerId) {
            $indexNames[] = \sprintf('%s_%s', $searchHandler->getIndexName(), $providerId);
        }

        return $indexNames;
    }

    /**
     * Fetch all provider ids.
     *
     * @return string[]
     */
    private function fetchAllProviderIds(): array
    {
        $providerIds = [];
        $providers = $this->entityManager->getRepository(Provider::class)->findAll();

        foreach ($providers as $provider) {
            $providerIds[] = $provider->getExternalId();
        }

        return $providerIds;
    }
}
