<?php

namespace Translate\Psr\Adapters;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    /**
     * @var \GuzzleHttp\Message\ResponseInterface
     */
    private $response;

    /**
     * Response constructor.
     * @param \GuzzleHttp\Message\ResponseInterface $response
     */
    public function __construct(\GuzzleHttp\Message\ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        $response = new \GuzzleHttp\Message\Response(
            $this->response->getStatusCode(),
            $this->response->getHeaders(),
            $this->response->getBody(),
            ['protocol_version' => $version, 'reason_phrase' => $this->response->getReasonPhrase()]
        );

        return new static($response);
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        return $this->response->getHeaderAsArray($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        return $this->response->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $response = clone $this->response;
        $response->setHeader($name, $value);

        return new static($response);
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $response = clone $this->response;
        if (!$response->hasHeader($name)) {
            $response->addHeader($name, $value);
        }

        return new static($response);
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $response = clone $this->response;
        $response->removeHeader($name);

        return new static($response);
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return new Stream($this->response->getBody());
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        throw new \LogicException('This method is not supported in current version');
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return (int)$this->response->getStatusCode();
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $response = clone $this->response;
        $response->setStatusCode($code);
        $response->setReasonPhrase($reasonPhrase);

        return new static($response);
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}