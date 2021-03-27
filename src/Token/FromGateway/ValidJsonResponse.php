<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use function json_encode;

final class ValidJsonResponse implements JsonResponseInterface
{
    /**
     * @var JsonResponseInterface
     */
    private $jsonResponse;

    /**
     * ValidJsonResponse constructor.
     * @param JsonResponseInterface $jsonResponse
     */
    public function __construct(JsonResponseInterface $jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;
    }

    /**
     * @inheritDoc
     */
    public function asKeyValue(): array
    {
        $response = $this->jsonResponse->asKeyValue();
        if (!isset($response['data']['token'])) {
            throw new \Exception(
                sprintf("Invalid json: %s", json_encode($response))
            );
        }
        return $response;
    }
}
