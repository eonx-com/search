<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\RequestProxyFactory;
use Zend\Diactoros\ServerRequest;
use function GuzzleHttp\Psr7\stream_for;

/**
 * @covers \LoyaltyCorp\Search\RequestProxyFactory
 */
final class RequestProxyFactoryTest extends TestCase
{
    /**
     * Tests the create createProxyRequest method when the configured elasticsearch
     * host contains a username and password.
     *
     * @return void
     */
    public function testCreateProxyRequestWithAuthentication(): void
    {
        $request = new ServerRequest(
            [],
            [],
            'https://subscriptions.system.example/search/index/_doc/_search?pp=5',
            'POST',
            stream_for('request body')
        );
        $request = $request->withAttribute('_encoder', 'value');

        $expectedUri = 'https://admin:password@127.0.0.3:9200/index/_doc/_search?pp=5';
        $expectedAuth = 'Basic ' . \base64_encode('admin:password');

        $instance = $this->getInstance();

        $result = $instance->createProxyRequest($request);

        self::assertSame($expectedUri, (string)$result->getUri());
        self::assertSame($expectedAuth, $result->getHeaderLine('Authorization'));
        self::assertSame('request body', (string)$result->getBody());
        self::assertInstanceOf(ServerRequest::class, $result);
        self::assertNull(($result instanceof ServerRequest) ? $result->getAttribute('_encoder') : '');
    }

    /**
     * Tests the create createProxyRequest method when the configured elasticsearch
     * host has no authentication details.
     *
     * @return void
     */
    public function testCreateProxyRequestWithoutAuthentication(): void
    {
        $request = new ServerRequest(
            [],
            [],
            'https://subscriptions.system.example/search/index/_doc/_search?pp=5',
            'POST',
            stream_for('request body')
        );
        $request = $request->withAttribute('_encoder', 'value');

        $expectedUri = 'https://127.0.0.4/index/_doc/_search?pp=5';

        $instance = $this->getInstance('https://127.0.0.4');

        $result = $instance->createProxyRequest($request);

        self::assertSame($expectedUri, (string)$result->getUri());
        self::assertSame('', $result->getHeaderLine('Authorization'));
        self::assertSame('request body', (string)$result->getBody());
        self::assertInstanceOf(ServerRequest::class, $result);
        self::assertNull(($result instanceof ServerRequest) ? $result->getAttribute('_encoder') : '');
    }

    /**
     * Returns the instance under test.
     *
     * @param string|null $elasticHost
     *
     * @return \LoyaltyCorp\Search\RequestProxyFactory
     */
    private function getInstance(?string $elasticHost = null): RequestProxyFactory
    {
        return new RequestProxyFactory($elasticHost ?? 'https://admin:password@127.0.0.3:9200');
    }
}
