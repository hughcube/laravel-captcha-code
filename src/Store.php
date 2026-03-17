<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:21 下午.
 */

namespace HughCube\Laravel\CaptchaCode;

use HughCube\Laravel\CaptchaCode\Generator\Generator;
use HughCube\Laravel\CaptchaCode\Storage\Storage;
use Illuminate\Support\Str;

class Store
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var int
     */
    protected $defaultTtl = 10 * 60;

    /**
     * @var string[]
     */
    protected $defaultCodes = [];

    /**
     * Store constructor.
     *
     * @param Storage   $storage
     * @param Generator $generator
     */
    public function __construct(Storage $storage, Generator $generator)
    {
        $this->storage = $storage;
        $this->generator = $generator;
    }

    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    public function withDefaultTtl(int $ttl): Store
    {
        $this->defaultTtl = $ttl;

        return $this;
    }

    public function withDefaultCodes(array $items): Store
    {
        $this->defaultCodes = $items;

        return $this;
    }

    public function buildKey(string $key): string
    {
        return $key;
    }

    public function getDefaultCode(string $key)
    {
        foreach ($this->defaultCodes as $pattern => $code) {
            if (Str::is($pattern, $key)) {
                return $code;
            }
        }

        return null;
    }

    public function getOrRand(string $key, $ttl = null)
    {
        return $this->getOrSet($key, null, $ttl);
    }

    public function getOrSet(string $key, $code = null, $ttl = null)
    {
        if (null != ($existCode = $this->get($key))) {
            return $existCode;
        }

        $code = $this->getDefaultCode($key) ?? $code ?? $this->generator->get();
        if (!$this->set($key, $code, $ttl)) {
            return false;
        }

        return $code;
    }

    public function get(string $key)
    {
        $key = $this->buildKey($key);

        return $this->storage->get($key) ?: null;
    }

    public function set(string $key, string $code, $ttl = null)
    {
        $ttl = null === $ttl ? $this->defaultTtl : $ttl;

        return $this->storage->set($key, $code, $ttl);
    }

    public function delete(string $key): bool
    {
        $key = $this->buildKey($key);

        return $this->storage->delete($key);
    }

    public function validate(string $key, $code, bool $deleteAfterSuccess = true): bool
    {
        if (empty($code)) {
            return false;
        }

        if (empty($existCode = $this->get($key))) {
            return false;
        }

        if (strtolower($existCode) !== strtolower($code)) {
            return false;
        }

        if ($deleteAfterSuccess) {
            $this->delete($key);
        }

        return true;
    }
}
