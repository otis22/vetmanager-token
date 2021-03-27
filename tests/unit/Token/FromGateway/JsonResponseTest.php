<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{

    public function testAsKeyValueWithNotEmptyJson(): void
    {
        $this->assertEquals(
            ['key' => 'value'],
            (
                new JsonResponse(
                    new class implements GatewayResponseInterface {
                        public function asString(): string
                        {
                            return '{"key":"value"}';
                        }
                    }
                )
            )->asKeyValue()
        );
    }
    public function testAsKeyValueWithInvalidJson(): void
    {
        $this->expectException(\Exception::class);
        $response = new JsonResponse(
            new class implements GatewayResponseInterface {
                public function asString(): string
                {
                    return '';
                }
            }
        );
        $response->asKeyValue();
    }
}
