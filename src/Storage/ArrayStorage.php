<?php

namespace Translate\Storage;

use Psr\SimpleCache\CacheInterface;

class ArrayStorage implements CacheInterface
{
    /**
     * @var array
     */
    private $_cache = [];

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return $this->_cache[$key][0];
        }
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        if ($ttl === null) {
            $duration = 0;
        } elseif ($ttl instanceof \DateInterval) {
            $duration = (new \DateTime())->add($ttl);
        } elseif (is_int($ttl)) {
            try {
                $ttl = new \DateInterval($ttl . 'S');
                $duration = (new \DateTime())->add($ttl);
            } catch (\Throwable $e) {
                throw new StorageException('Invalid key type: ' . $e->getMessage());
            }

        } else {
            throw new StorageException('Invalid key type of ' . print_r($key, 1));
        }

        $this->_cache[$key] = [$value, $duration];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        unset($this->_cache[$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->_cache = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($keys, $default);
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        return isset($this->_cache[$key]);
    }
}