<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Http\Controllers\V1;

use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\RequestProxyFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SearchController
{
    /**
     * @var \EoneoPay\Externals\HttpClient\Interfaces\ClientInterface
     */
    private $httpClient;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\RequestProxyFactoryInterface
     */
    private $requestProxyFactory;

    /**
     * Constructor.
     *
     * @param \EoneoPay\Externals\HttpClient\Interfaces\ClientInterface $httpClient
     * @param \LoyaltyCorp\Search\Interfaces\RequestProxyFactoryInterface $requestProxyFactory
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestProxyFactoryInterface $requestProxyFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestProxyFactory = $requestProxyFactory;
    }

    /**
     * Request a search against an elasticsearch index.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @IsGranted({"search:all"})
     */
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $searchRequest = $this->requestProxyFactory->createProxyRequest($request);

        $response = $this->httpClient->sendRequest($searchRequest);

        $response = $response
            // Remove all CORS headers, our application will re-add them
            ->withoutHeader('Access-Control-Allow-Origin')
            ->withoutHeader('Access-Control-Allow-Credentials')
            ->withoutHeader('Access-Control-Allow-Headers')
            ->withoutHeader('Access-Control-Allow-Methods')
            ->withoutHeader('Access-Control-Expose-Headers')
            ->withoutHeader('Access-Control-Max-Age');

        return $response;
    }
}
