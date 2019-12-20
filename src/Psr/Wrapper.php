<?php

namespace Translate\Psr;

use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Ring\Future\FutureInterface;
use Psr\Http\Message\ResponseInterface;
use Translate\ApiClient;
use Translate\Psr\Adapters\Response;

/**
 * @package Translate\Psr
 * @method FutureResponse requestAsync(string $method, string $uri, array $options = [])
 * @method FutureResponse|\GuzzleHttp\Message\ResponseInterface|FutureInterface|null requestRaw(string $method, string $uri, array $options = [])
 * @method bool setAlias(string $alias, $value)
 * @method bool hasAlias(string $alias)
 * @method bool removeAlias(string $alias)
 */
class Wrapper
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * ClientWrapper constructor.
     * @param ApiClient $client
     */
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $uri, $options);

        return new Response($response);
    }

    /**
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function reauthenticate(): self
    {
        $this->client->reauthenticate();

        return $this;
    }

    public function __call($name, $arguments)
    {
        return $this->client->$name(...$arguments);
    }
}