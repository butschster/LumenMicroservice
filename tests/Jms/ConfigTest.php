<?php

namespace Butschster\Tests\Jms;

use Butschster\Exchanger\Exceptions\ObjectMapperNotFound;
use Butschster\Exchanger\Jms\Config;
use Butschster\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var \Illuminate\Contracts\Config\Repository|\Mockery\MockInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mockConfigRepository();
    }

    function test_gets_handlers()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.handlers', [])->andReturn($handlers = ['foo', 'bar']);

        $this->assertEquals(
            $handlers,
            $this->makeConfig()->getHandlers()
        );
    }

    function test_gets_mapping_data()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn($data = ['foo', 'bar']);

        $this->assertEquals(
            $data,
            $this->makeConfig()->getMappingData()
        );
    }

    function test_gets_class_map()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn($data = ['foo' => ['bar']]);

        $this->assertEquals(
            ['bar'],
            $this->makeConfig()->getClassMap('foo')
        );
    }

    function test_if_class_map_not_found_null_should_be_returned()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn($data = ['foo' => ['bar']]);

        $this->assertNull(
            $this->makeConfig()->getClassMap('test')
        );
    }

    function test_find_payload_class_for_related_object_class()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn(['foo' => ['to' => 'bar']]);

        $this->assertEquals(
            'bar',
            $this->makeConfig()->findPayloadForRelatedObject('foo')
        );
    }

    function test_find_payload_class_for_related_object_class_by_alias()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn(['test' => ['aliases' => ['foo',], 'to' => 'bar']]);

        $this->assertEquals(
            'bar',
            $this->makeConfig()->findPayloadForRelatedObject('foo')
        );
    }

    function test_find_payload_class_for_related_object_should_throw_an_exception_when_map_not_found()
    {
        $this->expectException(ObjectMapperNotFound::class);
        $this->expectExceptionMessage('Mapper for class baz is not found.');

        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn(['test' => ['aliases' => ['foo',], 'to' => 'bar']]);

        $this->makeConfig()->findPayloadForRelatedObject('baz');
    }

    function test_find_related_class_for_payload()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn(['foo' => ['to' => 'bar']]);

        $this->assertEquals(
            'foo',
            $this->makeConfig()->findRelatedClassForPayload('bar')
        );
    }

    function test_find_related_class_for_payload_should_throw_an_exception_if_nothing_found()
    {
        $this->expectException(ObjectMapperNotFound::class);
        $this->expectExceptionMessage('Mapper for class baz is not found.');

        $this->repository->shouldReceive('get')
            ->once()->with('serializer.mapping', [])->andReturn(['foo' => ['to' => 'bar']]);

        $this->makeConfig()->findRelatedClassForPayload('baz');
    }

    protected function makeConfig()
    {
        return new Config($this->repository);
    }
}
