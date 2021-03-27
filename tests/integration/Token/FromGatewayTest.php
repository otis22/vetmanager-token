<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\Client;
use Otis22\VetmanagerToken\Credentials\AppName;
use Otis22\VetmanagerToken\Credentials\ByLoginPassword;
use Otis22\VetmanagerToken\Credentials\Login;
use Otis22\VetmanagerToken\Credentials\Password;
use Otis22\VetmanagerToken\Token\FromGateway\GatewayResponse;
use Otis22\VetmanagerToken\Token\FromGateway\JsonResponse;
use Otis22\VetmanagerToken\Token\FromGateway\ValidJsonResponse;
use Otis22\VetmanagerUrl\Url\Part\Domain;
use Otis22\VetmanagerUrl\Url\WithURI;
use PHPUnit\Framework\TestCase;
use Otis22\VetmanagerUrl\Url;

use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\not_empty_env;
use function Otis22\VetmanagerToken\token;

class FromGatewayTest extends TestCase
{
    public function testFacade(): void
    {
        $this->assertTrue(
            strlen(
                token(
                    credentials(
                        not_empty_env('TEST_LOGIN'),
                        not_empty_env('TEST_PASSWORD'),
                        "myapp"
                    ),
                    not_empty_env('TEST_DOMAIN_NAME')
                )->asString()
            ) > 5
        );
    }
    public function testFacadeWithWrongPassword(): void
    {
        try {
            token(
                credentials(
                    not_empty_env('TEST_LOGIN'),
                    'test password',
                    "myapp"
                ),
                not_empty_env('TEST_DOMAIN_NAME')
            )->asString();
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('пароль', $exception->getMessage());
        }
    }
}
