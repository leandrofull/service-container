<?php

namespace LeandroFull\ServiceContainer\Exception;

class UnableToInvokeException extends ServiceContainerException
{
    public function __construct(string $method)
    {
        parent::__construct("Unable to invoke method '$method' because it does not exist");
    }
}
