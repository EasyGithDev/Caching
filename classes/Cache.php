<?php

namespace Caching;

class Cache
{
    const CACHE_DIR = 'cache';

    private static $_instance = null;
    protected $dir = self::CACHE_DIR;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function __callStatic($name, $arguments)
    {
        $cache = Cache::getInstance();

        switch ($name) {
            case 'put':
                [$key, $content, $mtime] = $arguments;
                return $cache->putCache($key, $content, $mtime);
                break;
            case 'get':
                $key = $arguments[0];
                return $cache->getCache($key);
                break;
            case 'store':
                [$key, $lifeTime, $callback] = $arguments;
                return $cache->storeCache($key, $lifeTime, $callback);
                break;
            case 'forget':
                $key = $arguments[0];
                return $cache->forgetCache($key);
                break;
        }
    }

    public function putCache(string $key, string $content, int $mtime): bool
    {
        $filename = $this->getFilepath($key);
        $res = file_put_contents($filename, $content, LOCK_EX);
        touch($filename, $mtime);
        return $res;
    }

    public function getCache(string $key): string
    {
        return file_get_contents($this->getFilepath($key), LOCK_SH);
    }

    public function storeCache(string $key, int $lifeTime, callable $callback): mixed
    {
        $now = time();
        $filename = $this->getFilepath($key);
        $mtime = (file_exists($filename)) ? filemtime($filename) : 0;

        $content = '';
        if ($now >= $mtime) {
            $content = $callback();
            $mtime = $now + $lifeTime;
            $this->putCache($key, $this->serialize($content), $mtime);
        } else {
            $content = $this->unserialize($this->getCache($key));
        }
        return $content;
    }

    public function forgetCache(string $key): bool
    {
        $filename = $this->getFilepath($key);
        if (!file_exists($filename)) {
            return false;
        }
        return unlink($filename);
    }

    protected function getFilepath(string $key): string|false
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
