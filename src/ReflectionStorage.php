<?php

namespace LeandroFull\ServiceContainer;

class ReflectionStorage
{
    private array $reflections;

    /**
     * @param class-string $className
     * @return ?Reflection
     */
    public function getReflection(string $className): ?Reflection
    {
        if (isset($this->reflections[$className])) return $this->reflections[$className];

        try {
            $reflection = new Reflection($className);
            $this->reflections[$className] = $reflection;
            return $reflection;
        } catch(\Throwable) {
            $this->reflections[$className] = null;
            return null;
        }
    }
}
