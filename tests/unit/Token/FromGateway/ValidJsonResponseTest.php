<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use PHPUnit\Framework\TestCase;

class ValidJsonResponseTest extends TestCase
{
    public function testAsKeyValueWithValidJson(): void
    {
        $this->assertEquals(
            [
                'data' => ['token' => 'test']
            ],
            (
                new ValidJsonResponse(
                    new class implements JsonResponseInterface {
                        public function asKeyValue(): array
                        {
                            return [
                                'data' => ['token' => 'test']
                            ];
                        }
                    }
                )
            )->asKeyValue()
        );
    }
    public function testAsKeyValueWithInValidJson(): void
    {
        $this->expectException(\Exception::class);
        $response = new ValidJsonResponse(
            new class implements JsonResponseInterface {
                public function asKeyValue(): array
                {
                    return [];
                }
            }
        );
        $response->asKeyValue();
    }
}
