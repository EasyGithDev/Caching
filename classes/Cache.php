<?php

namespace Caching;

class Cache
{
    const CACHE_DIR = 'cache';

    protected $dir = self::CACHE_DIR;

    function put(string $key, string $content, int $mtime): bool
    {
        $filename = $this->getFilepath($key);
        $res = file_put_contents($filename, $content, LOCK_EX);
        touch($filename, $mtime);
        return $res;
    }

    function get(string $key): string
    {
        return file_get_contents($this->getFilepath($key), LOCK_SH);
    }

    function store(string $key, int $lifeTime, callable $callback): mixed
    {
        $now = time();
        $filename = $this->getFilepath($key);
        $mtime = (file_exists($filename)) ? filemtime($filename) : 0;

        $content = '';
        if ($now >= $mtime) {
            $content = $callback();
            $mtime = $now + $lifeTime;
            $this->put($key, $this->serialize($content), $mtime);
        } else {
            $content = $this->unserialize($this->get($key));
        }
        return $content;
    }

    function forget(string $key): bool
    {
        return unlink($this->getFilepath($key));
    }

    function getFilepath(string $key): string|false
    {
        $path = realpath($this->dir);
        return (!$path) ?: ($path . '/' . $this->hash($key));
    }

    protected function hash(string $key): string
    {
        return md5($key);
    }

    protected function serialize(mixed $content): string
    {
        return serialize($content);
    }

    protected function unserialize(string $data): mixed
    {
        return unserialize($data);
    }

    /**
     * Get the value of dir
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * Set the value of dir
     *
     * @return  self
     */
    public function setDir(string $dir): Cache
    {
        $this->dir = $dir;

        return $this;
    }
}
