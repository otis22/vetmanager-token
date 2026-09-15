<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use ElegantBro\Interfaces\Stringify;

final class Login implements Stringify
{
    /**
     * @var string
     */
    protected $login;

    /**
     * Login constructor.
     *
     * @param string $login
     */
    public function __construct(string $login)
    {
        $this->login = $login;
    }


    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        return $this->login;
    }
}
