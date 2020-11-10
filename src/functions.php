<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken;

use GuzzleHttp\Client;
use Otis22\VetmanagerToken\Credentials\AppName;
use Otis22\VetmanagerToken\Credentials\Login;
use Otis22\VetmanagerToken\Credentials\ByLoginPassword;
use Otis22\VetmanagerToken\Credentials\Password;

use function Otis22\VetmanagerUrl\url;

function credentials(string $login, string $password, string $app_name): Credentials
{
    return new ByLoginPassword(
        new Login($login),
        new Password($password),
        new AppName($app_name)
    );
}

function token(Credentials $credentials, string $domainName): Token
{
    return new Token\FromGateway(
        $credentials,
        url($domainName),
        new Client()
    );
}

function not_empty_env(string $env_name): string
{
    $value = getenv($env_name);
    if ($value === false) {
        throw new \Exception("{$env_name} can not be empty");
    }
    return $value;
}
