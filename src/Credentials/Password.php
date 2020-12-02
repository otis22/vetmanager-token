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

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        return $this->password;
    }
}
