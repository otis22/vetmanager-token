<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

final class JsonResponse implements JsonResponseInterface
{
    /**
     * @var GatewayResponseInterface
     */
    private $response;

    /**
     * JsonResponse constructor.
     * @param GatewayResponseInterface $response
     */
    public function __construct(GatewayResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function asKeyValue(): array
    {
        $responseText = $this->response->asString();
        $json = json_decode($this->response->asString(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid json response: {$responseText}");
        }
        return $json;
    }
}
