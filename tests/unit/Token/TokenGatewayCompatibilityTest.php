<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Otis22\VetmanagerUrl\Url\Concrete;
use Otis22\VetmanagerUrl\Url\FromJson;
use Otis22\VetmanagerToken\Url\BillingUrl;
use Otis22\VetmanagerToken\Url\Lazy;
use Otis22\VetmanagerUrl\Url;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\token_with_client;

class TokenGatewayCompatibilityTest extends TestCase
{
    public function testTokenRequestWithConfiguredClient(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"success":true,"data":{"token":"test-token"}}')
        ]));
        $handler->push(Middleware::history($history));
        $client = new Client([
            'handler' => $handler,
            'headers' => ['User-Agent' => 'example-service/1.0']
        ]);
        $token = token_with_client(
            credentials('test-login', 'TestPassword123', 'test-app'),
            new Concrete('https://clinic.example'),
            $client
        );
        $this->assertCount(0, $history);

        $this->assertSame('test-token', $token->asString());
        $this->assertSame('test-token', $token->asString());
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://clinic.example/token_auth.php', (string) $request->getUri());
        $this->assertSame('example-service/1.0', $request->getHeaderLine('User-Agent'));
        parse_str((string) $request->getBody(), $form);
        $this->assertSame(credentials('test-login', 'TestPassword123', 'test-app')->asKeyValue(), $form);
    }

    public function testBillingClientIsSeparateFromTokenClient(): void
    {
        if (!class_exists(FromJson::class) || !is_callable([FromJson::class, 'fromDomainAndBillingApiUsingClient'])) {
            $this->markTestSkipped('Configured Billing client requires vetmanager-url 1.1');
        }
        $billingHistory = [];
        $billingHandler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"success":true,"url":"clinic.example","protocol":"https"}')
        ]));
        $billingHandler->push(Middleware::history($billingHistory));
        $billingClient = new Client([
            'handler' => $billingHandler,
            'headers' => ['User-Agent' => 'example-service/1.0']
        ]);
        $tokenHistory = [];
        $tokenHandler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"data":{"token":"test-token"}}')
        ]));
        $tokenHandler->push(Middleware::history($tokenHistory));
        $tokenClient = new Client([
            'handler' => $tokenHandler,
            'headers' => ['User-Agent' => 'example-service/1.0', 'X-REST-API-KEY' => 'test-key']
        ]);
        $url = \Otis22\VetmanagerToken\Url\BillingUrl::resolve('compatibility-test', $billingClient);
        $token = token_with_client(
            credentials('test-login', 'TestPassword123', 'test-app'),
            $url,
            $tokenClient
        );

        $this->assertSame('test-token', $token->asString());
        $this->assertCount(1, $billingHistory);
        $this->assertCount(1, $tokenHistory);
        $request = $billingHistory[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(
            'https://billing-api.vetmanager.ru/host/compatibility-test',
            (string) $request->getUri()
        );
        $this->assertSame('example-service/1.0', $request->getHeaderLine('User-Agent'));
        $this->assertFalse($request->hasHeader('X-REST-API-KEY'));
        $this->assertSame('', (string) $request->getBody());
        $request = $tokenHistory[0]['request'];
        $this->assertSame('https://clinic.example/token_auth.php', (string) $request->getUri());
        $this->assertSame('test-key', $request->getHeaderLine('X-REST-API-KEY'));
        $this->assertSame('example-service/1.0', $request->getHeaderLine('User-Agent'));
    }

    /** @return array<array{bool}> */
    public function tokenImplementations(): array
    {
        return [[false], [true]];
    }

    /** @dataProvider tokenImplementations */
    public function testBillingFailurePreventsTokenRequest(bool $legacy): void
    {
        if (!class_exists(FromJson::class) || !is_callable([FromJson::class, 'fromDomainAndBillingApiUsingClient'])) {
            $this->markTestSkipped('Configured Billing client requires vetmanager-url 1.1');
        }
        $billingClient = new Client([
            'handler' => HandlerStack::create(new MockHandler([new Response(404, [], 'Unknown clinic')]))
        ]);
        $tokenHandler = new MockHandler([]);
        $url = new Lazy(function () use ($billingClient): Url {
            return BillingUrl::resolve('unknown-clinic', $billingClient);
        });
        $credentials = credentials('test-login', 'TestPassword123', 'test-app');
        $client = new Client(['handler' => HandlerStack::create($tokenHandler)]);
        $token = $legacy
            ? new FromGateway($credentials, $url, $client)
            : token_with_client($credentials, $url, $client);

        try {
            $token->asString();
            $this->fail('Expected Billing discovery to fail');
        } catch (\Exception $exception) {
            $previous = $exception->getPrevious();
            if (!$previous instanceof ClientException) {
                $this->fail('Expected a Billing HTTP client exception as the cause');
            }
            $this->assertSame(
                'https://billing-api.vetmanager.ru/host/unknown-clinic',
                (string) $previous->getRequest()->getUri()
            );
        }
        $this->assertNull($tokenHandler->getLastRequest());
    }
}
