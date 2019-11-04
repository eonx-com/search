<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use EoneoPay\Externals\HttpClient\Interfaces\ClientInterface;
use JsonException;
use LoyaltyCorp\Search\Exceptions\InvalidSearchRequestException;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use LoyaltyCorp\Search\Interfaces\ResponseFactoryInterface;
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
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string[] $accessTokens
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    private function addAccessControl(RequestInterface $request, ?array $accessTokens = null): RequestInterface
    {
        if ($request->getBody()->isSeekable() === true) {
            $request->getBody()->rewind();
        }

        // Extract request body contents
        $contents = \trim($request->getBody()->getContents());

        // If request body is an empty string, we force it to an empty json object
        // so the json_decode can continue.
        if ($contents === '') {
            $contents = '{}';
        }

        // Decode the JSON. If it fails we rethrow the exception wrapped in one of ours.
        try {
            $body = \json_decode(
                $contents,
                true,
                512,
                \JSON_THROW_ON_ERROR
            );
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (JsonException $exception) {
            throw new InvalidSearchRequestException(
                'An exception occurred while trying to decode the json request.',
                0,
                $exception
            );
        }

        // Exclude the access field from the _source key of results
        $body['_source'] = [
            'excludes' => [AccessTokenMappingHelper::ACCESS_TOKEN_PROPERTY],
        ];

        // If the search request doesnt have a query, we add a default match_all query.
        $query = $body['query'] ?? ['match_all' => []];

        // Wrap the entire query in a bool/filter
        $body['query'] = [
            'bool' => [
                'should' => $query,
                'filter' => [
                    [
                        'term' => [
                            AccessTokenMappingHelper::ACCESS_TOKEN_PROPERTY => $accessTokens ?: ['anonymous'],
                        ],
                    ],
                ],
            ],
        ];

        // Reencode the request body as a stream for the request.
        $modifiedBody = stream_for(\json_encode($body, \JSON_THROW_ON_ERROR));

        // Since Elasticsearch supports POST or GET for the same queries, force all requests
        // to POST since we always add a request body.
        return $request->withMethod('POST')
            ->withBody($modifiedBody);
    }
}
