<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use EoneoPay\Externals\HttpClient\Client;
use EoneoPay\Externals\HttpClient\ExceptionHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use LoyaltyCorp\Search\ResponseFactory;
use Zend\Diactoros\ServerRequest;
use function GuzzleHttp\Psr7\str;

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
        $service = new ResponseFactory();

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
        $request = new ServerRequest();

        $result = $service->sendRequest($client, $request);

        self::assertSame(str($expectedResponse), str($result));
    }
}
