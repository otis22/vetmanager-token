<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
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

    /**
     * @return string
     * @throws \Exception
     */
    private function token(): string
    {
        try {
            $response = $this->request();
            $json = $this->jsonFromResponse($response);
            if ($this->notValidStatusCode($response->getStatusCode())) {
                throw new \Exception($this->errorMessage($json));
            }
            return $json->data->token;
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(): ResponseInterface
    {
        try {
            return $this->client->request(
                "POST",
                $this->authUrl(),
                [
                    "form_params" => $this->credentials->asKeyValue()
                ]
            );
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $response = $exception->getResponse()) {
                return $response;
            }
            throw new \Exception(
                "Undefinded exception: " . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
    /**
     * @param \stdClass $json
     * @return string
     */
    private function errorMessage(\stdClass $json): string
    {
        return "title: {$json->title}, detail: {$json->detail}";
    }

    private function authUrl(): string
    {
        return $this->url->asString() . "/token_auth.php";
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
