<?php

namespace Translate\Psr\Adapters;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var \GuzzleHttp\Stream\StreamInterface
     */
    private $stream;

    /**
     * @param \GuzzleHttp\Stream\StreamInterface $stream
     */
    public function __construct(\GuzzleHttp\Stream\StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return (string)$this->stream;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->stream->close();
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * @inheritDoc
     */
    public function tell()
    {
        $result = $this->stream->tell();
        if ($result === false) {
            throw new \RuntimeException('Unable to find current position of stream');
        }
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $res = $this->stream->seek($offset, $whence);
        if ($res === false) {
            throw new \RuntimeException('Unable to seek offset: ' . $offset);
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    /**
     * @inheritDoc
     */
    public function write($string)
    {
        $res = $this->stream->write($string);
        if ($res === false) {
            throw new \RuntimeException('Failed to write to stream');
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    /**
     * @inheritDoc
     */
    public function read($length)
    {
        $res = $this->stream->read($length);
        if ($res === false) {
            throw new \RuntimeException('Failed to read from stream');
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function getContents()
    {
        return $this->stream->getContents();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }
}