# Dependency Injection Container

The Container class, located in the `DInjection\Container` namespace, serves as the core component of dependency management. It enables you to register services, resolve dependencies, handle singletons, and bind implementations to interfaces or abstract classes like Laravel `service container`. For understanding the logic behind laravel service container I decided to create my own.

## Getting Started

To begin using the Container, follow these steps:

1. **Installation**: Simply include the Container class in your project. There are no additional dependencies required.

   ```php
   $container = Container::getInstance();
   ```

2. **Registering Services**: Use the `register` method to add services to the container. You can register services using closures or class names.

    ```php
    $container->register('service', fn() => new SomeService());
    ```

3. **Resolving Dependencies**: Utilize the `get` method to resolve dependencies from the container. The container will automatically instantiate objects and inject dependencies as needed.

    ```php
    $service = $container->get('service');
    ```

4. **Handling Singletons**: If you need a service to be a singleton (i.e., a single instance shared across multiple requests), use the `singleton` method.

    ```php
    $container->singleton('service', fn() => new SomeService());
    ```

5. **Binding Implementations**: You can bind implementations to interfaces or abstract classes using the `register` or `singleton` methods.

    ```php
    $container->register(Writeable::class, Writer::class);
    ```

6. **Checking Service Existence**: Determine if a service is registered in the container using the `has` method.

    ```php
    if ($container->has('service')) {
        // Service exists
    }
    ```

7. **Exception Handling**: The container throws exceptions if it encounters issues, such as attempting to instantiate abstract types or non-existent services.
