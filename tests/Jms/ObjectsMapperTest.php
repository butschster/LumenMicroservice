<?php

namespace Butschster\Tests\Jms;

use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Exceptions\ObjectMapperNotFound;
use Butschster\Exchanger\Jms\Config;
use Butschster\Exchanger\Jms\ObjectsMapper;
use Butschster\Tests\TestCase;
use JMS\Serializer\Builder\DriverFactoryInterface;

class ObjectsMapperTest extends TestCase
{
    private \Mockery\MockInterface $driver;
    private \Mockery\MockInterface $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = $this->mock(DriverFactoryInterface::class);
        $this->serializer = $this->mockSerializer();
    }

    function test_converts_to_object_with_knowing_class()
    {
        $mapper = $this->makeMapper();
        $payload = new PayloadMapperTestPayload();

        $this->serializer->shouldReceive('serialize')
            ->once()->with($payload)->andReturn($serialized = '{hello:world}');
        $this->serializer->shouldReceive('deserialize')
            ->once()->with($serialized, PayloadMapperTestObject::class, [], $this->driver)
            ->andReturn($object = new PayloadMapperTestObject());

        $this->assertEquals($object, $mapper->toObject($payload));
    }

    function test_converts_to_object_with_passed_class()
    {
        $mapper = $this->makeMapper();
        $payload = new PayloadMapperTestPayload();

        $this->serializer->shouldReceive('serialize')
            ->once()->with($payload)->andReturn($serialized = '{hello:world}');
        $this->serializer->shouldReceive('deserialize')
            ->once()->with($serialized, PayloadMapperTestObject2::class, [], $this->driver)
            ->andReturn($object = new PayloadMapperTestObject2());

        $this->assertEquals($object, $mapper->toObject($payload, PayloadMapperTestObject2::class));
    }

    function test_converts_to_object_with_unknown_class_should_throw_an_exception()
    {
        $this->expectException(ObjectMapperNotFound::class);
        $this->expectExceptionMessage('Mapper for class Butschster\Tests\Jms\PayloadMapperTestPayload2 is not found.');

        $mapper = $this->makeMapper();
        $payload = new PayloadMapperTestPayload2();

        $mapper->toObject($payload);
    }

    function test_converts_to_payload_with_knowing_class()
    {
        $mapper = $this->makeMapper();
        $object = new PayloadMapperTestObject();

        $this->serializer->shouldReceive('serialize')
            ->once()->with($object, [], $this->driver)->andReturn($serialized = '{hello:world}');

        $this->serializer->shouldReceive('deserialize')
            ->once()->with($serialized, PayloadMapperTestPayload::class)
            ->andReturn($payload = new PayloadMapperTestPayload());

        $this->assertEquals($payload, $mapper->toPayload($object));
    }

    function test_converts_to_payload_with_passed_class()
    {
        $mapper = $this->makeMapper();
        $object = new PayloadMapperTestObject();

        $this->serializer->shouldReceive('serialize')
            ->once()->with($object, [], $this->driver)->andReturn($serialized = '{hello:world}');

        $this->serializer->shouldReceive('deserialize')
            ->once()->with($serialized, PayloadMapperTestPayload2::class)
            ->andReturn($payload = new PayloadMapperTestPayload2());

        $this->assertEquals($payload, $mapper->toPayload($object, PayloadMapperTestPayload2::class));
    }

    function test_converts_to_payload_with_unknown_class_should_throw_an_exception()
    {
        $this->expectException(ObjectMapperNotFound::class);
        $this->expectExceptionMessage('Mapper for class Butschster\Tests\Jms\PayloadMapperTestObject2 is not found.');

        $mapper = $this->makeMapper();
        $object = new PayloadMapperTestObject2();

        $mapper->toPayload($object);
    }

    function test_converts_to_array()
    {
        $object = new PayloadMapperTestObject();
        $mapper = $this->makeMapper();
        $array = ['hello' => 'world'];

        $this->serializer->shouldReceive('serialize')->once()->with($object)->andReturn('{"hello":"world"}');

        $this->assertEquals($array, $mapper->toArray($object));
    }

    function makeMapper()
    {
        $repo = $this->mockConfigRepository();

        $repo->shouldReceive('get')->zeroOrMoreTimes()->andReturn([
            PayloadMapperTestObject::class => [
                'to' => PayloadMapperTestPayload::class,
                'attributes' => [
                    // ...
                ],
            ],
        ]);

        return new ObjectsMapper(
            $this->driver,
            $this->serializer,
            new Config($repo)
        );
    }
}

class PayloadMapperTestObject
{
}

class PayloadMapperTestObject2
{
}

class PayloadMapperTestPayload implements Payload
{
}

class PayloadMapperTestPayload2 implements Payload
{
}
