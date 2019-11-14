<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use EoneoPay\Externals\HttpClient\Client;
use EoneoPay\Externals\HttpClient\ExceptionHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use LoyaltyCorp\Search\Exceptions\InvalidSearchRequestException;
use LoyaltyCorp\Search\ResponseFactory;
use stdClass;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use function GuzzleHttp\Psr7\str;
use function GuzzleHttp\Psr7\stream_for;

/**
 * @covers \LoyaltyCorp\Search\ResponseFactory
 */
final class ResponseFactoryTest extends TestCase
{
    /**
     * Test send request strips out the CORS headers.
     *
     * @return void
     */
    public function testSendRequest(): void
    {
        $response = new Response(200, [
            'Access-Control-Allow-Origin' => 'Yep',
            'Access-Control-Allow-Credentials' => 'Yep',
            'Access-Control-Allow-Headers' => 'Yep',
            'Access-Control-Allow-Methods' => 'Yep',
            'Access-Control-Expose-Headers' => 'Yep',
            'Access-Control-Max-Age' => 'Yep',
        ]);

        $expectedResponse = new Response();

        $client = new Client(new GuzzleClient(['handler' => new MockHandler([$response])]), new ExceptionHandler());
        $service = new ResponseFactory($client);

        $request = new ServerRequest([], [], null, 'POST', stream_for('{}'));

        $result = $service->sendRequest($request);

        self::assertSame(str($expectedResponse), str($result));
    }

    /**
     * Tests what happens when an empty request body is proxied.
     *
     * @return void
     */
    public function testAccessControlEmptyBody(): void
    {
        $response = new TextResponse('RESPONSE');

        $mockHandler = new MockHandler([$response]);
        $client = new Client(
            new GuzzleClient([
                'handler' => $mockHandler,
            ]),
            new ExceptionHandler()
        );
        $service = new ResponseFactory($client);

        $expectedData = [
            '_source' => [
                'excludes' => [
                    '_access_tokens',
                ],
            ],
            'query' => [
                'bool' => [
                    'must' => [
                        'match_all' => new stdClass(),
                    ],
                    'filter' => [
                        ['terms' => ['_access_tokens' => ['anonymous']]],
                    ],
                ],
            ],
        ];
        $expectedRequest = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for(
                \json_encode($expectedData, \JSON_THROW_ON_ERROR)
            )
        );

        $request = new ServerRequest([], [], null, 'POST', stream_for(''));

        $service->sendRequest($request);

        $actual = $mockHandler->getLastRequest()
            ->withoutHeader('user-agent');

        self::assertSame(str($expectedRequest), str($actual));
    }

    /**
     * Tests what happens when an empty request body is proxied.
     *
     * @return void
     */
    public function testAccessControl(): void
    {
        $response = new TextResponse('RESPONSE');

        $mockHandler = new MockHandler([$response]);
        $client = new Client(
            new GuzzleClient([
                'handler' => $mockHandler,
            ]),
            new ExceptionHandler()
        );
        $service = new ResponseFactory($client);

        $expectedData = [
            'query' => [
                'bool' => [
                    'must' => [
                        'term' => [
                            'user' => [
                                'value' => 'tim',
                            ],
                        ],
                    ],
                    'filter' => [
                        ['terms' => ['_access_tokens' => ['access-secret', 'purple-elephants']]],
                    ],
                ],
            ],
            '_source' => [
                'excludes' => [
                    '_access_tokens',
                ],
            ],
        ];
        $expectedRequest = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for(
                \json_encode($expectedData, \JSON_THROW_ON_ERROR)
            )
        );

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for('{"query": {"term": {"user": {"value": "tim"}}}}')
        );

        $service->sendRequest($request, ['access-secret', 'purple-elephants']);

        $actual = $mockHandler->getLastRequest()
            ->withoutHeader('user-agent');

        self::assertSame(str($expectedRequest), str($actual));
    }

    /**
     * Tests what happens when an empty request body is proxied.
     *
     * @return void
     */
    public function testJsonNotConvertedToEmptyArray(): void
    {
        $response = new TextResponse('RESPONSE');

        $mockHandler = new MockHandler([$response]);
        $client = new Client(
            new GuzzleClient([
                'handler' => $mockHandler,
            ]),
            new ExceptionHandler()
        );
        $service = new ResponseFactory($client);

        $expectedData = [
            'query' => [
                'bool' => [
                    'must' => [
                        'match_all' => new stdClass(),
                    ],
                    'filter' => [
                        ['terms' => ['_access_tokens' => ['access-secret', 'purple-elephants']]],
                    ],
                ],
            ],
            '_source' => [
                'excludes' => [
                    '_access_tokens',
                ],
            ],
        ];
        $expectedRequest = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for(
                \json_encode($expectedData, \JSON_THROW_ON_ERROR)
            )
        );

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for('{"query": {"match_all": {}}}')
        );

        $service->sendRequest($request, ['access-secret', 'purple-elephants']);

        $actual = $mockHandler->getLastRequest()
            ->withoutHeader('user-agent');

        self::assertSame(str($expectedRequest), str($actual));
    }

    /**
     * Tests what happens when a request contains invalid json.
     *
     * @return void
     */
    public function testAccessControlBadJson(): void
    {
        $mockHandler = new MockHandler([]);
        $client = new Client(
            new GuzzleClient([
                'handler' => $mockHandler,
            ]),
            new ExceptionHandler()
        );
        $service = new ResponseFactory($client);

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            stream_for('invalid[]')
        );

        $this->expectException(InvalidSearchRequestException::class);
        $this->expectExceptionMessage('An exception occurred while trying to decode the json request.');

        $service->sendRequest($request);
    }
}
