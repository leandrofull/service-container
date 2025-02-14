<?php

namespace LeandroFull\ServiceContainer;

class Reflection
{
    private readonly \ReflectionClass $class;

    private ?self $parent;

    private array $methods;

    public function __construct(string|\ReflectionClass $class)
    {
        $this->class = is_string($class) ? new \ReflectionClass($class) : $class;
    }

    /**
     * @param string $methodName
     * @return ?\ReflectionMethod
     */
    public function getMethod(string $methodName): ?\ReflectionMethod
    {
        if (isset($this->methods[$methodName])) return $this->methods[$methodName];

        try {
            $method = $this->class->getMethod($methodName);
            $this->methods[$methodName] = $method;
            return $method;
        } catch(\ReflectionException) {
            $this->methods[$methodName] = null;
            return null;
        }
    }

    /**
     * @return ?\ReflectionMethod
     */
    public function getConstructor(): ?\ReflectionMethod
    {
        return $this->getMethod('__construct');
    }

    /**
     * @return \ReflectionClass
     */
    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }

    /**
     * @return ?Reflection
     */
    public function getParent(): ?self
    {
        if (isset($this->parent)) return $this->parent;

        $reflection = $this->class->getParentClass();

        $this->parent = !$reflection ? null : new self($reflection);

        return $this->parent;
    }
}
