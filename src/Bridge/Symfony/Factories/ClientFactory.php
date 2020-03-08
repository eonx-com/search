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
     * @var null|int
     */
    private $connectionTimeout;

    /**
     * @var string
     */
    private $elasticsearchHost;

    /**
     * @var \EoneoPay\Externals\Logger\Interfaces\LoggerInterface
     */
    private $logger;

    /**
     * @var null|int
     */
    private $timeout;

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
     * @param null|int $connectionTimeout
     * @param null|int $timeout
     */
    public function __construct(
        ClientBulkResponseHelperInterface $bulkResponseHelper,
        LoggerInterface $logger,
        ?string $elasticsearchHost = null,
        ?bool $verifySsl = null,
        ?int $connectionTimeout = null,
        ?int $timeout = null
    ) {
        $this->bulkResponseHelper = $bulkResponseHelper;
        $this->logger = $logger;
        $this->elasticsearchHost = $elasticsearchHost ?? '';
        $this->verifySsl = $verifySsl ?? true;
        $this->connectionTimeout = $connectionTimeout ?? 2;
        $this->timeout = $timeout ?? 12;
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
                        'connect_timeout' => $this->connectionTimeout,
                        'timeout' => $this->timeout,
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
