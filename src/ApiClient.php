<?php

namespace Translate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function array_merge_recursive;

class ApiClient
{
    use ResolvesAliases;

    public const DEFAULT_API_URL = 'http://dev-api.translate.center/api/v1/';

    /**
     * @var array
     */
    protected $options;

    /**
     * @var CacheInterface
     */
    protected $storage;

    /**
     * @var Client|null
     */
    protected $httpClient;

    /**
     * @param array $options
     * @param CacheInterface $storage
     */
    public function __construct(array $options, CacheInterface $storage)
    {
        $this->processOptions($options);
        $this->storage = $storage;
    }

    /**
     * @param array $options
     */
    protected function processOptions(array $options): void
    {
        if (!isset($options['login'])) {
            throw new \InvalidArgumentException('Login is required!');
        }
        if (!isset($options['password'])) {
            throw new \InvalidArgumentException('Password is required!');
        }
        $options['api'] = $options['api'] ?? static::DEFAULT_API_URL;
        $options['maxAttempts'] = $options['maxAttempts'] ?? 3;

        $this->options = $options;
    }

    /**
     * @return Client
     */
    public function httpClient(): Client
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client(['base_uri' => $this->options['api']]);
        }
        return $this->httpClient;
    }

    /**
     * Sends authenticated request to API
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        static $attempt = 0;
        $this->ensureAuth();
        $options = array_merge_recursive($this->getDefaultOptions(), $options);
        $request = $this->httpClient()->createRequest($method, $this->resolveAliases($uri), $options);

        try {
            $response = $this->httpClient()->send($request);
        } catch (RequestException $exception) {
            if ($attempt < $this->options['maxAttempts'] && $exception->getCode() === 401) {
                ++$attempt;
                return $this->reauthenticate()->request($method, $uri, $options);
            }

            throw $exception;
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        return [
            'headers' => [
                'Authorization' => $this->resolveAliases('{authToken}'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'future' => false
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return FutureResponse
     * @throws InvalidArgumentException
     */
    public function requestAsync(string $method, string $uri, array $options = []): FutureResponse
    {
        $this->ensureAuth();
        $options = array_merge_recursive($this->getDefaultOptions(), ['future' => true], $options);
        $request = $this->httpClient()->createRequest($method, $this->resolveAliases($uri), $options);

        return $this->httpClient()->send($request);
    }

    /**
     * Sends request without any data manipulation
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return FutureResponse|ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     */
    public function rawRequest(string $method, string $uri, array $options = [])
    {
        $request = $this->httpClient()->createRequest($method, $uri, $options);

        return $this->httpClient()->send($request);
    }

    /**
     * @param bool $forceReauthenticate
     * @throws InvalidArgumentException
     */
    protected function ensureAuth($forceReauthenticate = false): void
    {
        if (!$forceReauthenticate && $this->hasAlias('authToken') && $this->hasAlias('userUuid')) {
            return;
        }

        $request = $this->httpClient()->createRequest('POST', 'login', array_merge($this->getDefaultOptions(), [
            'json' => [
                'login' => $this->options['login'],
                'password' => $this->options['password']
            ],
        ]));

        $data = $this->httpClient()->send($request)->json();
        $this->setAlias('authToken', $data['authToken']);
        $this->setAlias('userUuid', $data['userUuid']);
    }

    /**
     * @return $this
     * @throws InvalidArgumentException
     */
    public function reauthenticate(): self
    {
        $this->ensureAuth(true);

        return $this;
    }
}
