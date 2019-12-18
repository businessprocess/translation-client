<?php

namespace Translate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function array_merge_recursive;

class ApiClient
{
    use ResolvesAliases;

    /**
     * @var array
     */
    private $options;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @param CacheInterface $cache
     * @param array $options
     */
    public function __construct(CacheInterface $cache, array $options = [])
    {
        $this->processOptions($options);
        $this->cache = $cache;
        $this->httpClient = new Client(['base_uri' => $this->options['api']]);
    }

    /**
     * @param array $options
     */
    private function processOptions(array &$options): void
    {
        if (!isset($options['login'])) {
            throw new \InvalidArgumentException('Login is required!');
        }
        if (!isset($options['password'])) {
            throw new \InvalidArgumentException('Password is required!');
        }
        if (!isset($options['api'])) {
            throw new \InvalidArgumentException('Api url is required!');
        }
        $this->options = $options;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $this->ensureAuth();
        $options = array_merge_recursive($options, ['headers' => ['Authorization' => $this->resolveAliases('{authToken}')]]);

        return $this->httpClient->request($method, $this->resolveAliases($uri), $options);
    }

    /**
     * @param bool $forceReauthenticate
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function ensureAuth($forceReauthenticate = false): void
    {
        if (!$forceReauthenticate && $this->hasAlias('authToken') && $this->hasAlias('userUuid')) {
            return;
        }

        $resp = $this->httpClient->request('POST', 'login', [
            'json' => [
                'login' => $this->options['login'],
                'password' => $this->options['password']
            ]
        ]);

        $data = \GuzzleHttp\json_decode($resp->getBody(), true);
        $this->setAlias('authToken', $data['authToken']);
        $this->setAlias('userUuid', $data['userUuid']);
    }
}
