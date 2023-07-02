<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use ElegantBro\Interfaces\Stringify;

final class Password implements Stringify
{
    /**
     * @var string
     */
    private $password;

    /**
     * Password constructor.
     *
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    private function isNotValid(): bool
    {
        return !preg_match(
            "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/im",
            $this->password
        );
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        if ($this->isNotValid()) {
            throw new \Exception("Password is not valid. "
                . "Password must contains digits and latin letters and can't be less than 8 symbols");
        }
        return $this->password;
    }
}
