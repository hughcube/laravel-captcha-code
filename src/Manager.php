<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:19 下午.
 */

namespace HughCube\Laravel\CaptchaCode;

use Closure;
use HughCube\Laravel\CaptchaCode\Generator\DefaultGenerator;
use HughCube\Laravel\CaptchaCode\Generator\Generator;
use HughCube\Laravel\CaptchaCode\Storage\CacheStorage;
use HughCube\Laravel\CaptchaCode\Storage\Storage;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class Manager extends \HughCube\Laravel\ServiceSupport\Manager
{
    /**
     * @var Closure[]
     */
    protected $storageCustomCreators = [];

    /**
     * @var Closure[]
     */
    protected $generatorCustomCreators = [];

    /**
     * Get a client by name.
     *
     * @param string|null|int $name
     *
     * @return Store
     */
    public function store($name = null): Store
    {
        return $this->driver($name);
    }

    public function extendStorage($driver, Closure $callback): Manager
    {
        $this->storageCustomCreators[$driver] = $callback;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return Storage
     */
    protected function makeStorage(array $config): Storage
    {
        if (empty($driver = $config['driver'] ?? 'cache')) {
            throw new InvalidArgumentException('The store drive must be defined.');
        }

        if (isset($this->storageCustomCreators[$driver])) {
            return call_user_func($this->storageCustomCreators[$driver], $config);
        }

        $driverMethod = 'create'.ucfirst($driver).'StorageDriver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException(sprintf('The stored drive "%s" is not defined.', $driver));
    }

    /**
     * @param array $config
     *
     * @return Storage
     */
    protected function createCacheStorageDriver(array $config)
    {
        $cache = $config['cache'] ?? null;
        $cache = is_object($cache) ? $cache : Cache::store($cache);

        return new CacheStorage($cache);
    }

    public function extendGenerator($driver, Closure $callback): Manager
    {
        $this->generatorCustomCreators[$driver] = $callback;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return Generator
     */
    public function makeGenerator(array $config): Generator
    {
        if (empty(($driver = $config['driver'] ?? 'default'))) {
            throw new InvalidArgumentException('The generator drive must be defined.');
        }

        if (isset($this->generatorCustomCreators[$driver])) {
            return call_user_func($this->generatorCustomCreators[$driver], $config);
        }

        $method = 'create'.ucfirst($driver).'GeneratorDriver';
        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException(sprintf('The generator drive "%s" is not defined.', $driver));
    }

    public function createDefaultGeneratorDriver(array $config): DefaultGenerator
    {
        return new DefaultGenerator(
            $config['length'],
            ($config['string'] ?? null),
            ($config['nonZeroLeading'] ?? $config['non_zero_leading'] ?? false)
        );
    }

    public function getDriversConfigKey(): string
    {
        return 'stores';
    }

    protected function makeDriver(array $config): Store
    {
        $storage = $this->makeStorage(($config['storage'] ?? []));
        $generator = $this->makeGenerator(($config['generator'] ?? []));

        $store = new Store($storage, $generator);

        return $store
            ->withDefaultTtl(($config['defaultTtl'] ?? 10 * 60))
            ->withDefaultCodes(($config['defaultCodes'] ?? []));
    }

    protected function getPackageFacadeAccessor(): string
    {
        return CaptchaCode::getFacadeAccessor();
    }
}
