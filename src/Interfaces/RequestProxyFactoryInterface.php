<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use Psr\Http\Message\RequestInterface;

interface RequestProxyFactoryInterface
{
    /**
     * Takes a RequestInterface made to the application and rewrites headers
     * and hostnames to send the request to the configured Elasticsearch system.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createProxyRequest(RequestInterface $request): RequestInterface;
}
