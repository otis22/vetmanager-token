<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use ElegantBro\Interfaces\Stringify;
use Psr\Http\Message\ResponseInterface;

final class ValidStatusResponse implements Stringify
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * ValidStatusResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        if (in_array($this->code(), [401, 500])) {
            throw new \Exception(
                sprintf(
                    "Invalid response status: %s. %s",
                    $this->code(),
                    (
                        new ErrorMessage(
                            $this->body()
                        )
                    )->asString()
                )
            );
        }
        return $this->body();
    }

    /**
     * @return string
     */
    private function body(): string
    {
        return strval($this->response->getBody());
    }

    /**
     * @return int
     */
    private function code(): int
    {
        return $this->response->getStatusCode();
    }
}
