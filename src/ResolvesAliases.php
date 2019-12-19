<?php

namespace Translate;

use InvalidArgumentException;
use function preg_replace_callback;

trait ResolvesAliases
{
    /**
     * @var array
     */
    public static $aliasMap = [
        'userUuid' => 'translator.userUuid',
        'projectUuid' => 'translator.projectUuid'
    ];

    /**
     * @param string $uri
     * @return string
     */
    protected function resolveAliases(string $uri): string
    {
        return preg_replace_callback('/{(?<alias>\w+)}/', function ($matches) {
            $resolved = $this->storage->get($this->mapAlias($matches['alias']));
            if ($resolved === null) {
                throw new InvalidArgumentException('No value stored for alias: ' . $matches['alias']);
            }
        }, $uri);
    }

    /**
     * @param string $alias
     * @return string|null
     */
    protected function mapAlias(string $alias): ?string
    {
        return static::$aliasMap[$alias] ?? 'translator.' . $alias;
    }

    /**
     * @param string $alias
     * @param $value
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setAlias(string $alias, $value): bool
    {
        return $this->storage->set($this->mapAlias($alias), $value);
    }

    /**
     * @param string $alias
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function hasAlias(string $alias): bool
    {
        return $this->storage->has($this->mapAlias($alias));
    }

    /**
     * @param string $alias
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function removeAlias(string $alias): bool
    {
        return $this->storage->delete($this->mapAlias($alias));
    }
}
