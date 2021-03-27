<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ValidStatusResponseTest extends TestCase
{

    public function testReponseWithValidStatusCode(): void
    {
        $this->assertEquals(
            'body',
            (
                new ValidStatusResponse(
                    new Response(
                        200,
                        [],
                        'body'
                    )
                )
            )->asString()
        );
    }
    public function testResponseWithInvalidStatusCode(): void
    {
        $this->expectException(\Exception::class);
        $response = new ValidStatusResponse(
            new Response(
                500,
                [],
                'body'
            )
        );
        $response->asString();
    }
}
