<?php

namespace LeandroFull\ServiceContainer;

use LeandroFull\ServiceContainer\Exception\UnableToInvokeException;

class ServiceContainer
{
    private array $instances;

    private array $callbacks;

    private readonly ReflectionStorage $reflStorage;

    public function __construct()
    {
        $this->reflStorage = new ReflectionStorage();
    }

    private function resolveArgument(\ReflectionParameter $param): mixed
    {
       $paramType = $param->getType();

       if (!$paramType instanceof \ReflectionNamedType) {
            if ($param->isDefaultValueAvailable()) return $param->getDefaultValue();
            throw new \Exception();
       }

       if ($paramType->isBuiltin()) {
            if ($param->isDefaultValueAvailable()) return $param->getDefaultValue();
            if ($param->allowsNull()) return null;
            throw new \Exception();
       }

       $instance = $this->get($paramType->getName());

       if ($instance) return $instance;

       if ($param->allowsNull()) return null;

       throw new \Exception();
    }

    private function resolveArguments(\ReflectionMethod $method): ?array
    {
       $methodValues = [];

       $methodParams = $method->getParameters();

       foreach ($methodParams as $methodParam) {
            try {
                $methodValues[] = $this->resolveArgument($methodParam);
            } catch(\Exception) {
                return null;
            }
       }

        return $methodValues;
    }

    /**
     * @param class-string $className
     * @return ?object
     */
    public function tryCreateInstanceOf(string $className): ?object
    {
        if (!class_exists($className)) return null;

        $reflection = $this->reflStorage->getReflection($className);

        $instance = $reflection->getClass()->newInstanceWithoutConstructor();

        $constructor = $reflection->getConstructor();

        if ($constructor) {
            $constructorArgs = $this->resolveArguments($constructor);

            if (!$constructorArgs) return null;

            $constructor->invokeArgs($instance, $constructorArgs);
        }

        return $instance;
    }

    /**
     * @param string $alias
     * @return ?object
     */
    public function get(string $alias): ?object
    {
        if (isset($this->instances[$alias])) return $this->instances[$alias];

        if (isset($this->callbacks[$alias])) {
            $instance = $this->callbacks[$alias]();

            $this->instances[$alias] = $instance;

            try {
                if (!isset($this->instances[$instance::class])) 
                    $this->instances[$instance::class] = $instance;

                return $instance;
            } catch(\Throwable) {
                $this->instances[$alias] = null;
                return null;
            }
        }

        $instance = $this->tryCreateInstanceOf($alias);

        if (!$instance) {
            $this->instances[$alias] = null;
            return null;
        }

        $this->instances[$alias] = $instance;

        return $instance;
    }

    /**
     * @param string $alias
     * @param callable $callback fn() => object
     * @return ServiceContainer
     */
    public function set(string $alias, callable $callback): static
    {
        $this->callbacks[$alias] = $callback;
        return $this;
    }

    /**
     * @param object $instance
     * @param string $method
     * @return mixed
     * @throws UnableToInvokeException
     */
    public function invoke(object $instance, string $method): mixed
    {
        $reflMethod =  $this->reflStorage->getReflection($instance::class)->getMethod($method);
        if (!$reflMethod) throw new UnableToInvokeException($method);
        return $reflMethod->invokeArgs($instance, $this->resolveArguments($reflMethod));
    }

    /**
     * @return object[]
     */
    public function getInstances(): array
    {
        return $this->instances;
    }
}
