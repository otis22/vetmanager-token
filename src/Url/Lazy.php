<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Url;

use Otis22\VetmanagerUrl\Url;

/** @internal */
final class Lazy implements Url
{
    /** @var callable(): Url */
    private $resolve;

    /** @var Url|null */
    private $url;

    /** @param callable(): Url $resolve */
    public function __construct(callable $resolve)
    {
        $this->resolve = $resolve;
    }

    public function asString(): string
    {
        if ($this->url === null) {
            $url = ($this->resolve)();
            $value = $url->asString();
            $this->url = $url;
            return $value;
        }
        return $this->url->asString();
    }
}
