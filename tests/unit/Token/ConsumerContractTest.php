<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Otis22\VetmanagerToken\Credentials\AppName;
use Otis22\VetmanagerToken\Token;
use Otis22\VetmanagerUrl\Url\Concrete as ConcreteUrl;
use PHPUnit\Framework\TestCase;

use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\token;
use function Otis22\VetmanagerToken\token_with_client;

class ConsumerContractTest extends TestCase
{
    public function testTypesUsedByRestApiConsumer(): void
    {
        $appName = new AppName('example-app');
        $token = new Concrete('example-token');

        $this->assertSame('example-app', $appName->asString());
        $this->assertInstanceOf(Token::class, $token);
        $this->assertSame('example-token', $token->asString());
        $this->assertSame('example-token', $token->asString());
        $this->assertSame(
            ['login' => 'example-login', 'password' => 'ExamplePassword123', 'app_name' => 'example-app'],
            credentials('example-login', 'ExamplePassword123', 'example-app')->asKeyValue()
        );
    }

    public function testDefaultTokenFacadeCanBeConstructedWithoutBilling(): void
    {
        $token = token(
            credentials('example-login', 'ExamplePassword123', 'example-app'),
            'http://clinic.example:invalid'
        );

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInstanceOf(FromGateway::class, $token);
    }

    /** @return array<array{bool}> */
    public function implementations(): array
    {
        return [[true], [false]];
    }

    /** @dataProvider implementations */
    public function testPostIsDeferredAndSuccessfulTokenIsCached(bool $legacy): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"data":{"token":"example-token"}}')
        ]));
        $handler->push(Middleware::history($history));
        $client = new Client([
            'handler' => $handler,
            'headers' => ['X-Consumer-Test' => 'present']
        ]);
        $auth = credentials('example-login', 'ExamplePassword123', 'example-app');
        $url = new ConcreteUrl('https://clinic.example');
        $token = $legacy
            ? new FromGateway($auth, $url, $client)
            : token_with_client($auth, $url, $client);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertCount(0, $history);
        $this->assertSame('example-token', $token->asString());
        $this->assertSame('example-token', $token->asString());
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://clinic.example/token_auth.php', (string) $request->getUri());
        $this->assertSame('present', $request->getHeaderLine('X-Consumer-Test'));
        parse_str((string) $request->getBody(), $form);
        $this->assertSame($auth->asKeyValue(), $form);
    }

    /** @return array<array{bool, int, string}> */
    public function failedResponses(): array
    {
        return [
            [true, 401, '{"title":"invalid","detail":"invalid"}'],
            [false, 401, '{"title":"invalid","detail":"invalid"}'],
            [true, 200, 'invalid-json'],
            [false, 200, 'invalid-json']
        ];
    }

    /** @dataProvider failedResponses */
    public function testFailedResponseIsNotCached(bool $legacy, int $status, string $body): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response($status, [], $body),
            new Response(200, [], '{"data":{"token":"recovered-token"}}')
        ]));
        $handler->push(Middleware::history($history));
        $client = new Client(['handler' => $handler]);
        $auth = credentials('example-login', 'ExamplePassword123', 'example-app');
        $url = new ConcreteUrl('https://clinic.example');
        $token = $legacy
            ? new FromGateway($auth, $url, $client)
            : token_with_client($auth, $url, $client);

        $this->assertCount(0, $history);
        try {
            $token->asString();
            $this->fail('Expected the first token response to fail');
        } catch (\Exception $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }
        $this->assertCount(1, $history);
        $this->assertSame('recovered-token', $token->asString());
        $this->assertSame('recovered-token', $token->asString());
        $this->assertCount(2, $history);
    }
}
