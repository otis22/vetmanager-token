<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use ElegantBro\Interfaces\Stringify;

use function json_decode;

final class ErrorMessage implements Stringify
{
    /**
     * @var string
     */
    private $body;

    /**
     * ErrorMessage constructor.
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        $json = json_decode($this->body, true);
        if (isset($json['title']) && isset($json['detail'])) {
            return sprintf(
                "title: %s, detail: %s",
                $json['title'],
                $json['detail']
            );
        }
        return sprintf("Can't parse response errors: %s", $this->body);
    }
}
