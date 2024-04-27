<?php

namespace DInjection\Container;

use Closure;
use DInjection\Container\Exception\CouldNotResolveAbstraction;
use DInjection\Container\Exception\CouldNotResolveClassException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container implements ContainerInterface
{
    protected array $services = [];

    protected array $instances = [];

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (! (self::$instance instanceof Container)) {
            self::$instance = new Container();
        }

        return self::$instance;
    }


    public function register(string $key, mixed $value, bool $singleton = false): void
    {
        if (is_string($value) && class_exists($value)) {
            $value = fn() => new $value;
        }

        if ($singleton) {
            $this->instances[$key] = null;
        }

        $this->services[$key] = $value;
    }


    public function singleton(string $key, mixed $callback): void
    {
        $this->register($key, $callback, true);
    }

    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            $serviceResolver = $this->services[$id];

            $serviceResolver = $serviceResolver instanceof Closure
                ? $serviceResolver()
                : $serviceResolver;

            if (array_key_exists($id, $this->instances)) {

                if ($this->instances[$id] instanceof $serviceResolver) {
                    return $this->instances[$id];
                }

                $this->instances[$id] = $serviceResolver;
            }

            return $serviceResolver;
        }

        return $this->build($id);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    protected function build(string $service): object
    {
        try {
            $reflector = new ReflectionClass($service);
        } catch (ReflectionException $e) {
            throw new CouldNotResolveClassException("Service does not founded!");
        }

        if (! $reflector->isInstantiable()) {
            throw new CouldNotResolveAbstraction("Can not instantiate an abstract type");
        }

        $parameters = $reflector?->getConstructor()?->getParameters() ?? [];

        $resolvedDependencies = array_map(function (ReflectionParameter $parameter) {
            $class = $parameter->getType()->getName();

            if (class_exists($class)) {
                return self::build($class);
            }

        }, $parameters);

        return $reflector->newInstanceArgs($resolvedDependencies);
    }

}