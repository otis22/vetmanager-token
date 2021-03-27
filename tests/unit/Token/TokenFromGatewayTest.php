<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use Otis22\VetmanagerToken\Token\FromGateway\JsonResponseInterface;
use PHPUnit\Framework\TestCase;

class TokenFromGatewayTest extends TestCase
{

    public function testValidTokenResponse(): void
    {
        $this->assertEquals(
            "add2da284cb3cd670729df1695065e9768a4f409",
            (
                new FromGateway(
                    new class implements JsonResponseInterface {
                        public function asKeyValue(): array
                        {
                            return [
                                'data' => [
                                    'token' => 'add2da284cb3cd670729df1695065e9768a4f409'
                                ]
                            ];
                        }
                    }
                )
            )->asString()
        );
    }
}
