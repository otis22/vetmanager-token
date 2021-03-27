<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use GuzzleHttp\ClientInterface;
use Otis22\VetmanagerToken\Credentials;
use Otis22\VetmanagerUrl\Url\WithURI;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

final class GatewayResponse implements GatewayResponseInterface
{
    /**
     * @var WithURI
     */
    private $tokenAuthUrl;
    /**
     * @var Credentials
     */
    private $credentials;
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * GatewayResponse constructor.
     * @param WithURI $tokenAuthUrl
     * @param Credentials $credentials
     * @param ClientInterface $client
     */
    public function __construct(WithURI $tokenAuthUrl, Credentials $credentials, ClientInterface $client)
    {
        $this->tokenAuthUrl = $tokenAuthUrl;
        $this->credentials = $credentials;
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */

    public function asString(): string
    {
        return (
            new ValidStatusResponse(
                $this->request()
            )
        )->asString();
    }

    /**
     * @return ResponseInterface
     */
    private function request(): ResponseInterface
    {
        try {
             return $this->client->request(
                 "POST",
                 $this->tokenAuthUrl->asString(),
                 [
                    "form_params" => $this->credentials->asKeyValue()
                 ]
             );
        } catch (RequestException $e) {
            if ($e->hasResponse() && $response = $e->getResponse()) {
                return $response;
            }
            throw new \Exception(
                "Undefinded exception: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
