<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Http\Controllers\V1;

use EoneoPay\Externals\HttpClient\Client;
use EoneoPay\Externals\HttpClient\ExceptionHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use LoyaltyCorp\Search\Bridge\Laravel\Http\Controllers\V1\SearchController;
use LoyaltyCorp\Search\RequestProxyFactory;
use Tests\LoyaltyCorp\Search\TestCase;
use Zend\Diactoros\ServerRequest;
use function GuzzleHttp\Psr7\str;
use function GuzzleHttp\Psr7\stream_for;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Http\Controllers\V1\SearchController
 */
class SearchControllerTest extends TestCase
{
    /**
     * Test search method is defined in the controller.
     *
     * @return void
     */
    public function testSearch(): void
    {
        $request = new ServerRequest(
            [],
            [],
            'https://search.example/search/transactions/_search',
            'POST'
        );

        $response = new Response();
        $controller = $this->getInstance($response);

        $result = $controller->search($request);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame(str($response), str($result));
    }

    /**
     * Tests the search action removes CORS headers from the response.
     *
     * @return void
     */
    public function testSearchStripsCors(): void
    {
        $request = new ServerRequest(
            [],
            [],
            'https://subscriptions.system.example/transactions/_doc/_search?pp=5',
            'POST',
            stream_for()
        );

        $response = new Response(200, [
            'Access-Control-Allow-Origin' => 'Yep',
            'Access-Control-Allow-Credentials' => 'Yep',
            'Access-Control-Allow-Headers' => 'Yep',
            'Access-Control-Allow-Methods' => 'Yep',
            'Access-Control-Expose-Headers' => 'Yep',
            'Access-Control-Max-Age' => 'Yep'
        ]);

        $expectedResponse = new Response();
        $controller = $this->getInstance($response);

        $result = $controller->search($request);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame(str($expectedResponse), str($result));
    }

    /**
     * Returns the controller under test.
     *
     * @param \GuzzleHttp\Psr7\Response $response
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Http\Controllers\V1\SearchController
     */
    private function getInstance(Response $response): SearchController
    {
        return new SearchController(
            new Client(
                new GuzzleClient(['handler' => new MockHandler([$response])]),
                new ExceptionHandler()
            ),
            new RequestProxyFactory('https://localhost:9200')
        );
    }
}
