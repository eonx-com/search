<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * Sends request and strips out CORS headers before returning a response.
     *
     * @param \EoneoPay\Externals\HttpClient\Interfaces\ClientInterface $client
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest(ClientInterface $client, RequestInterface $request): ResponseInterface;
}
