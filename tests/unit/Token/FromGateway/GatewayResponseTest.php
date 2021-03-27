<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Otis22\VetmanagerToken\Credentials\FakeCredentials;
use Otis22\VetmanagerUrl\Url;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class GatewayResponseTest extends TestCase
{
    public function testAsStringWithValidResponse(): void
    {
        $this->assertEquals(
            "body",
            (
                new GatewayResponse(
                    new Url\WithURI(
                        new Url\Concrete('https://fake.url'),
                        'test/path'
                    ),
                    new FakeCredentials(),
                    new Client(
                        [
                            'handler' => HandlerStack::create(
                                new MockHandler(
                                    [
                                        new Response(
                                            200,
                                            [],
                                            'body'
                                        )
                                    ]
                                )
                            )
                        ]
                    )
                )
            )->asString()
        );
    }

    public function testAsStringWithInvalidResponse(): void
    {
        $this->expectException(\Exception::class);
        $response = new GatewayResponse(
            new Url\WithURI(
                new Url\Concrete('https://fake.url'),
                'test/path'
            ),
            new FakeCredentials(),
            new Client(
                [
                    'handler' => HandlerStack::create(
                        new MockHandler(
                            [
                                new RequestException(
                                    'Error Communicating with Server',
                                    new Request('GET', 'test'),
                                    new Response(500, [], '')
                                )
                            ]
                        )
                    )
                ]
            )
        );
        $response->asString();
    }
}
