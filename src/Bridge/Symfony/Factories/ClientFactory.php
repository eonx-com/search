<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Factories;

use Elasticsearch\ClientBuilder;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\ClientFactoryInterface;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;

final class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface
     */
    private $bulkResponseHelper;

    /**
     * @var string
     */
    private $elasticsearchHost;

    /**
     * @var \EoneoPay\Externals\Logger\Interfaces\LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $verifySsl;

    /**
     * ClientFactory constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface $bulkResponseHelper
     * @param \EoneoPay\Externals\Logger\Interfaces\LoggerInterface $logger
     * @param null|string $elasticsearchHost
     * @param null|bool $verifySsl
     */
    public function __construct(
        ClientBulkResponseHelperInterface $bulkResponseHelper,
        LoggerInterface $logger,
        ?string $elasticsearchHost = null,
        ?bool $verifySsl = null
    ) {
        $this->bulkResponseHelper = $bulkResponseHelper;
        $this->logger = $logger;
        $this->elasticsearchHost = (string)($elasticsearchHost ?? '');
        $this->verifySsl = (bool)($verifySsl ?? true);
    }

    /**
     * Create search client.
     *
     * @return \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    public function create(): ClientInterface
    {
        return new Client(
            ClientBuilder::create()
                ->setConnectionParams([
                    'client' => [
                        'connect_timeout' => 2,
                        'timeout' => 12,
                    ],
                ])
                ->setLogger($this->logger)
                ->setHosts(\array_filter([$this->elasticsearchHost]))
                ->setSSLVerification($this->verifySsl)
                ->build(),
            $this->bulkResponseHelper
        );
    }
}
