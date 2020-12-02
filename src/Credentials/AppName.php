<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use ElegantBro\Interfaces\Stringify;

final class AppName implements Stringify
{
    /**
     * @var string
     */
    private $appName;

    /**
     * AppName constructor.
     *
     * @param string $appName
     */
    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        return $this->appName;
    }
}
