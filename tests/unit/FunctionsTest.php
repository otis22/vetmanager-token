<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Otis22\VetmanagerUrl\Url\Concrete;
use Otis22\VetmanagerToken\Token\FromGateway;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testCredentialsReturnInstanceOfCredentials(): void
    {
        $this->assertTrue(credentials('test', 'test', 'test') instanceof Credentials);
    }

    public function testTokenReturnInstanceOfToken(): void
    {
        $this->assertTrue(
            token(
                credentials('test', 'test', 'test'),
                'test'
            ) instanceof Token
        );
    }

    public function testTokenDefersInvalidDomainErrorUntilUsed(): void
    {
        $token = token(credentials('test', 'TestPassword123', 'test'), 'http://clinic.example:invalid');
        $this->assertInstanceOf(Token::class, $token);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Can't parse url or host string");
        $token->asString();
    }

    public function testTokenFacadeRetriesFailedPostAndCachesSuccess(): void
    {
        $token = token(credentials('test', 'TestPassword123', 'test'), 'clinic');
        $this->assertInstanceOf(FromGateway::class, $token);
        /** @var FromGateway $token */
        $url = new \ReflectionProperty(FromGateway::class, 'url');
        $url->setAccessible(true);
        $url->setValue($token, new Concrete('https://clinic.example'));

        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(401, [], '{"title":"denied","detail":"denied"}'),
            new Response(200, [], '{"data":{"token":"recovered-token"}}')
        ]));
        $handler->push(Middleware::history($history));
        $client = new \ReflectionProperty(FromGateway::class, 'client');
        $client->setAccessible(true);
        $client->setValue($token, new Client(['handler' => $handler]));

        $this->assertCount(0, $history);
        $failed = false;
        try {
            $token->asString();
        } catch (\Exception $exception) {
            $failed = true;
            $this->assertNotSame('', $exception->getMessage());
        }
        $this->assertTrue($failed, 'Expected the first token response to fail');
        $this->assertCount(1, $history);
        $this->assertSame('recovered-token', $token->asString());
        $this->assertSame('recovered-token', $token->asString());
        $this->assertCount(2, $history);
    }
}
