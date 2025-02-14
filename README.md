# Service Container

- PHP 8.2

- Reflection API Extension

<code>composer require leandrofull/service-container</code>

## php tests/run

```php

use LeandroFull\Test\Assert;
use LeandroFull\ServiceContainer\ServiceContainer;

interface UserRepository
{
}

class RealUserRepository implements UserRepository
{
}

abstract class Controller
{
    public function __construct(protected readonly UserRepository $userRepository) {}
}

class HomeController extends Controller
{
    public function index(UserRepository $userRepository): void
    {
        Assert::same(RealUserRepository::class, $userRepository::class);
    }
}

$container = new ServiceContainer();

$controller = $container->get(HomeController::class);

Assert::same(null, $controller);

$container->set(UserRepository::class, fn(): UserRepository => new RealUserRepository());

$controller = $container->get(HomeController::class);

Assert::same(HomeController::class, $controller::class);

$container->invoke($controller, 'index');

```

### Result:

```
Assertions: 3
Ok: 3

OK!
```