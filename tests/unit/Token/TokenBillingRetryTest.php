<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Otis22\VetmanagerToken\Url\BillingUrl;
use Otis22\VetmanagerToken\Url\Lazy;
use Otis22\VetmanagerUrl\Url;
use Otis22\VetmanagerUrl\Url\BillingApi;
use Otis22\VetmanagerUrl\Url\FromJson;
use Otis22\VetmanagerUrl\Url\FromBillingApiGateway;
use Otis22\VetmanagerUrl\Url\Part\Domain;
use PHPUnit\Framework\TestCase;

use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\token_with_client;

class TokenBillingRetryTest extends TestCase
{
    /** @return array<array{string, bool}> */
    public function failedResponses(): array
    {
        $cases = [];
        foreach (['invalid-json', '{"success":false}', '{"success":true,"url":"","protocol":"https"}'] as $body) {
            $cases[] = [$body, false];
            $cases[] = [$body, true];
        }
        return $cases;
    }

    /** @dataProvider failedResponses */
    public function testSameTokenRetriesAfterInvalidBillingResponse(string $body, bool $legacy): void
    {
        $billingHandler = new MockHandler([
            new Response(200, [], $body),
            new Response(200, [], '{"success":true,"url":"clinic.example","protocol":"https"}')
        ]);
        $billingClient = new Client(['handler' => HandlerStack::create($billingHandler)]);
        $url = new Lazy(function () use ($billingClient): Url {
            if (class_exists(FromJson::class)) {
                return BillingUrl::resolve('clinic', $billingClient);
            }
            if (class_exists(FromBillingApiGateway::class)) {
                $legacyUrl = new FromBillingApiGateway(
                    new BillingApi('https://billing.example'),
                    new Domain('clinic'),
                    $billingClient
                );
                if ($legacyUrl instanceof Url) {
                    return $legacyUrl;
                }
            }
            throw new \LogicException('Unsupported test dependency');
        });
        $tokenHandler = new MockHandler([new Response(200, [], '{"data":{"token":"test-token"}}')]);
        $client = new Client(['handler' => HandlerStack::create($tokenHandler)]);
        $credentials = credentials('a', 'Abc12345!', 'test-app');
        $token = $legacy
            ? new FromGateway($credentials, $url, $client)
            : token_with_client($credentials, $url, $client);

        try {
            $token->asString();
        } catch (\Exception $exception) {
            $this->assertNotSame('', $exception->getMessage());
            $this->assertCount(1, $billingHandler);
            $this->assertNull($tokenHandler->getLastRequest());
            $this->assertSame('test-token', $token->asString());
            $this->assertSame('test-token', $token->asString());
            $this->assertCount(0, $billingHandler);
            $this->assertCount(0, $tokenHandler);
            return;
        }
        $this->fail('Expected invalid Billing response to fail');
    }
}
