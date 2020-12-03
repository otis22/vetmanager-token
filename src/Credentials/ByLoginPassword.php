<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use Otis22\VetmanagerToken\Credentials;

final class ByLoginPassword implements Credentials
{
    /**
     * @var Login
     */
    private $login;
    /**
     * @var Password
     */
    private $password;
    /**
     * @var AppName
     */
    private $appName;

    /**
     * Credentials constructor.
     * @param Login $login
     * @param Password $password
     * @param AppName $appName
     */
    public function __construct(Login $login, Password $password, AppName $appName)
    {
        $this->login = $login;
        $this->password = $password;
        $this->appName = $appName;
    }

    /**
     * @inheritDoc
     */
    public function asKeyValue(): array
    {
        return [
            "login" => $this->login->asString(),
            "password" => $this->password->asString(),
            "app_name" => $this->appName->asString(),
        ];
    }
}
