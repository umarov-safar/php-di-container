<?php

namespace Tests;

use Closure;
use DInjection\Container\Container;
use DInjection\Container\Exception\CouldNotResolveAbstraction;
use DInjection\Container\Exception\CouldNotResolveClassException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();
    }

    public function test_it_allows_you_to_register_services_using_closures()
    {
        $container = Container::getInstance();

        $container->register('service', fn() => new SomeService());

        $this->assertTrue($container->has('service'));
        $this->assertInstanceOf(SomeService::class, $container->get('service'));
        $this->assertNotSame($container->get('service'), $container->get('service'));
    }


    public function test_it_allows_you_to_register_servicees_using_string()
    {

        $this->container->register('service', 'some string');

        $this->assertEquals(
            $this->container->get('service'), 'some string'
        );
    }

    public function test_it_preserves_the_container_instance()
    {
        $firstInstance = Container::getInstance();
        $secondInstance = Container::getInstance();

        $this->assertSame($firstInstance, $secondInstance);
    }

    public function test_it_persists_services_between_instances()
    {
        $this->container->register('service', fn() => new SomeService());

        $this->assertInstanceOf(SomeService::class, Container::getInstance()->get('service'));
    }


    public function test_it_throws_an_exception_if_we_pass_a_serive_that_does_not_exist()
    {
        $this->expectException(CouldNotResolveClassException::class);


        Container::getInstance()->get(NotExistsClass::class);
    }

    public function test_it_inject_dependencies()
    {
        $user = Container::getInstance()->get(User::class);

        $this->assertInstanceOf(ORM::class, $user->orm);
    }


    public function test_it_injects_multiple_dependnecies()
    {
        $userMultiParam = Container::getInstance()->get(UserMultiParameter::class);

        $this->assertInstanceOf(ORM::class, $userMultiParam->orm);
        $this->assertInstanceOf(SomeService::class, $userMultiParam->service);
    }

    public function test_it_injects_nested_dependencies()
    {
        $userMultiParam = Container::getInstance()->get(CreateUserAccount::class);

        $this->assertInstanceOf(SomeService::class, $userMultiParam->service);
        $this->assertInstanceOf(User::class, $userMultiParam->user);
        $this->assertInstanceOf(ORM::class, $userMultiParam->user->orm);
    }


    #[DataProvider('singletonsTestProvider')]
    public function test_it_allows_us_to_register_singletons(string $key, Closure $service)
    {
        $this->container->singleton($key, $service);

        $this->assertSame($this->container->get($key), $this->container->get($key));
    }

    public static function singletonsTestProvider(): array
    {
        return [
            ['service', fn() => new SomeService()],
            [SomeService::class, fn() => new SomeService()],
        ];
    }


    public function test_it_can_bind_implementations_to_interfaces()
    {
        $this->container->register(Writeable::class, Writer::class);

        $this->assertInstanceOf(Writer::class, $this->container->get(Writeable::class));
    }

    public function test_it_can_bind_implementations_to_interfaces_an_abstractions()
    {
        $this->container->singleton(Writeable::class, Writer::class);

        $this->assertInstanceOf(Writer::class, $this->container->get(Writeable::class));
    }


    public function test_it_throws_an_exception_if_instantiate_an_interface(): void
    {
        $this->expectException(CouldNotResolveAbstraction::class);
        Container::getInstance()->get(ContainerInterface::class);
    }
}


class SomeService {

}

class ORM {

}

class CreateUserAccount
{
    public function __construct(
        public User $user,
        public SomeService $service,
    )
    {
    }
}

class User {

    public ORM $orm;

    public function __construct(ORM $orm)
    {
        $this->orm = $orm;
    }
}

class UserMultiParameter
{
    public function __construct(
        public ORM $orm,
        public SomeService $service
    )
    {

    }
}


interface Writeable {

}


class Writer implements Writeable {

}