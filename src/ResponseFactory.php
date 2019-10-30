<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\ResponseFactoryInterface;
use LoyaltyCorp\Search\Interfaces\Access\AccessPopulatorInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\stream_for;

final class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var \EoneoPay\Externals\HttpClient\Interfaces\ClientInterface
     */
    private $client;

    /**
     * ResponseFactory constructor.
     *
     * @param \EoneoPay\Externals\HttpClient\Interfaces\ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(
        RequestInterface $request,
        ?array $accessTokens = null
    ): ResponseInterface {
        $request = $this->addAccessControl($request, $accessTokens);
        $response = $this->client->sendRequest($request);

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

    /**
     * Injects access control related parts into the search request.
     *
     * @param RequestInterface $request
     * @param array $accessTokens
     *
     * @return RequestInterface
     */
    private function addAccessControl(RequestInterface $request, array $accessTokens): RequestInterface
    {
        if ($request->getBody()->isSeekable() === true) {
            $request->getBody()->rewind();
        }

        $body = \json_decode(
            $request->getBody()->getContents(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        // If the search request doesnt have
        $query = $body['query'] ?? ['match_all' => []];

        // Exclude the access field from the _source key of results
        $body['_source'] = [
            'excludes' => [AccessPopulatorInterface::ACCESS_TOKEN_PROPERTY]
        ];

        // Wrap the entire query in a bool/filter
        $body['query'] = [
            'bool' => [
                'should' => $query
            ],
            'filter' => [
                [
                    'term' => [
                        AccessPopulatorInterface::ACCESS_TOKEN_PROPERTY => $accessTokens ?: ['anonymous']
                    ]
                ]
            ]
        ];

        $modifiedBody = stream_for(\json_encode($body, \JSON_THROW_ON_ERROR));

        return $request->withMethod('POST')
            ->withBody($modifiedBody);
    }
}
