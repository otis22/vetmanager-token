<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\Client;
use Otis22\VetmanagerToken\Credentials\AppName;
use Otis22\VetmanagerToken\Credentials\ByLoginPassword;
use Otis22\VetmanagerToken\Credentials\Login;
use Otis22\VetmanagerToken\Credentials\Password;
use Otis22\VetmanagerUrl\Url\Part\Domain;
use PHPUnit\Framework\TestCase;
use Otis22\VetmanagerUrl\Url;

use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\not_empty_env;
use function Otis22\VetmanagerToken\token;

class FromGatewayTest extends TestCase
{
    public function testAsString(): void
    {
        $this->assertTrue(
            strlen(
                (
                    new FromGateway(
                        new ByLoginPassword(
                            new Login(not_empty_env('TEST_LOGIN')),
                            new Password(not_empty_env('TEST_PASSWORD')),
                            new AppName('myapp')
                        ),
                        new Url\FromBillingApiGateway(
                            new Url\BillingApi(
                                'https://billing-api.vetmanager.cloud'
                            ),
                            new Domain(
                                not_empty_env('TEST_DOMAIN_NAME')
                            ),
                            new Client()
                        ),
                        new Client()
                    )
                )->asString()
            ) > 5
        );
    }
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
}
