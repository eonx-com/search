<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\ResponseFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendRequest(ClientInterface $client, RequestInterface $request): ResponseInterface
    {
        $response = $client->sendRequest($request);

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
