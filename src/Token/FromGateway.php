<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\ClientInterface;
use Otis22\VetmanagerToken\Token;
use Otis22\VetmanagerUrl\Url;
use Psr\Http\Message\ResponseInterface;
use Otis22\VetmanagerToken\Credentials;

final class FromGateway implements Token
{
    /**
     * @var Credentials
     */
    private $credentials;
    /**
     * @var Url
     */
    private $url;
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $token;

    /**
     * TokenFromGateway constructor.
     *
     * @param Credentials $credentials
     * @param Url $url
     * @param ClientInterface $client
     */
    public function __construct(Credentials $credentials, Url $url, ClientInterface $client)
    {
        $this->credentials = $credentials;
        $this->url = $url;
        $this->client = $client;
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

    private function token(): string
    {
        try {
            $response = $this->client->request(
                "POST",
                $this->authUrl(),
                [
                    "form_params" => $this->credentials->asKeyValue()
                ]
            );
            $json = $this->jsonFromResponse($response);
            if ($this->notValidStatusCode($response->getStatusCode())) {
                throw new \Exception($json->title);
            }
            return $json->data->token;
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function authUrl(): string
    {
        return $this->url . "/token_auth.php";
    }

    private function notValidStatusCode(int $status): bool
    {
        return in_array($status, [401, 500]);
    }

    private function jsonFromResponse(ResponseInterface $response): \stdClass
    {
        $responseText = strval($response->getBody());
        $json = \json_decode($responseText);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid json response: {$responseText}");
        }
        return $json;
    }
}
