<?php

namespace Caching;

class Cache
{
    const CACHE_DIR = 'cache';

    protected static $_instance = null;
    protected $dir;
    protected $debug;

    protected function __construct()
    {
        $this->dir = self::CACHE_DIR;
        $this->debug = null;
    }

    public static function getInstance()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    public static function __callStatic($name, $arguments)
    {
        $cache = static::getInstance();

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

    public function putCache(string $key, string $content, int $mtime): int|false
    {
        if (empty($key)) {
            throw new ArgumentException("Key can not be empty");
        }

        if (empty($content)) {
            throw new ArgumentException("Content can not be empty");
        }

        if ($mtime < 1) {
            throw new ArgumentException("Mtime must be greater or equal to 1");
        }

        $filename = $this->getFilepath($key);
        $res = file_put_contents($filename, $content, LOCK_EX);
        if (!touch($filename, $mtime)) {
            return false;
        }
        return $res;
    }

    public function getCache(string $key): string|false
    {
        if (empty($key)) {
            throw new ArgumentException("Key can not be empty");
        }

        return file_get_contents($this->getFilepath($key), LOCK_SH);
    }

    public function storeCache(string $key, int $lifeTime, callable $callback): mixed
    {
        if (empty($key)) {
            throw new ArgumentException("Key can not be empty");
        }

        if ($lifeTime < 1) {
            throw new ArgumentException("Lifetime must be greater or equal to 1");
        }

        if (is_null($callback)) {
            throw new ArgumentException("Callback can not be null");
        }

        $now = time();
        $filename = $this->getFilepath($key);
        $mtime = (file_exists($filename)) ? filemtime($filename) : 0;

        $msg = sprintf("now:%d >= mtime:%d (%b)\n", $now, $mtime, ($now >= $mtime));
        $this->debug?->msg($msg);

        $content = '';
        if ($now >= $mtime) {
            $this->debug?->msg("PUT\n");
            $content = $callback();
            $mtime = $now + $lifeTime;
            if (!$this->putCache($key, $this->serialize($content), $mtime)) {
                throw new CacheIoException("Unable to write cache : $filename");
            }
        } else {
            $this->debug?->msg("GET\n");
            if (($serialized = $this->getCache($key)) === false) {
                throw new CacheIoException("Unable to read cache : $filename");
            }
            $content = $this->unserialize($serialized);
        }
        return $content;
    }

    public function forgetCache(string $key): bool
    {
        if (empty($key)) {
            throw new ArgumentException("Key can not be empty");
        }

        $filename = $this->getFilepath($key);
        if (!file_exists($filename)) {
            return false;
        }
        return unlink($filename);
    }

    protected function getFilepath(string $key): string
    {
        $path = realpath($this->dir);
        if (!is_dir($path)) {
            throw new CacheDirException($this->dir);
        }
        return $path . DIRECTORY_SEPARATOR . $this->hash($key);
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

    /**
     * Set the value of debug
     *
     * @return  self
     */
    public function setDebug(Debug $debug)
    {
        $this->debug = $debug;

        return $this;
    }
}
