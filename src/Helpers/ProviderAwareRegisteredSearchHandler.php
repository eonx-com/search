<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Multitenancy\Database\Entities\Provider;
use LoyaltyCorp\Search\Interfaces\Helpers\ProviderAwareRegisteredSearchHandlerInterface;

class ProviderAwareRegisteredSearchHandler extends RegisteredSearchHandler
    implements ProviderAwareRegisteredSearchHandlerInterface
{
    /**
     * Provider ids.
     *
     * @var mixed[]
     */
    private $providerIds;

    /**
     * ProviderAwareRegisteredSearchHandler constructor.
     *
     * @param mixed[] $searchHandlers Search handlers
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager Entity manager
     */
    public function __construct(array $searchHandlers, EntityManagerInterface $entityManager)
    {
        parent::__construct($searchHandlers);

        $this->populateProviderIds($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProviderIds(): array
    {
        return $this->providerIds;
    }

    /**
     * Populate provider ids.
     *
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager
     *
     * @return void
     */
    private function populateProviderIds(EntityManagerInterface $entityManager): void
    {
        $providers = $entityManager->getRepository(Provider::class)->findAll();

        foreach ($providers as $provider) {
            $this->providerIds[] = $provider->getExternalId();
        }
    }
}
