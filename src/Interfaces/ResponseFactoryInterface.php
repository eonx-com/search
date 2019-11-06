<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * Sends request and strips out CORS headers before returning a response.
     *
     * Pass in an array of access tokens that the request should be granted that
     * can be built based on the authenticated user, the multi tenant identifier or
     * other conditions that should limit the search results to anything that only
     * contains the access tokens specified. The tokens are considered an or list
     * and any match will allow the document to be returned.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string[]|null $accessTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest(
        RequestInterface $request,
        ?array $accessTokens = null
    ): ResponseInterface;
}
