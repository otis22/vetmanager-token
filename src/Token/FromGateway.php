<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use Otis22\VetmanagerToken\Token;
use Otis22\VetmanagerToken\Token\FromGateway\JsonResponseInterface;

final class FromGateway implements Token
{
    /**
     * @var JsonResponseInterface
     */
    private $jsonResponse;
    /**
     * @var string
     */
    private $token;

    /**
     * FromGateway constructor.
     * @param JsonResponseInterface $jsonResponse
     */
    public function __construct(JsonResponseInterface $jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        if (empty($this->token)) {
            $this->token = $this->token();
        }
        return $this->token;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function token(): string
    {
        try {
            $json = $this->jsonResponse->asKeyValue();
            return $json['data']['token'];
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
