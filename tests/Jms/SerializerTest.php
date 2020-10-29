<?php

namespace Butschster\Tests\Jms;

use Butschster\Exchanger\Jms\Serializer;
use Butschster\Tests\TestCase;
use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Annotation as JMS;

class SerializerTest extends TestCase
{
    private \Mockery\MockInterface $builder;
    /** @var \Butschster\Exchanger\Contracts\Exchange\Config|\Mockery\MockInterface */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->mockSerializerConfig();
    }

    function test_serialize()
    {
        $payload = new SerializerTestPayload();
        $payload->body = 'test';

        $json = $this->makeSerializer()->serialize($payload, []);
        $this->assertEquals('{"body":"test"}', $json);
    }

    function test_deserialize()
    {
        $payload = $this->makeSerializer()->deserialize('{"body":"test"}', SerializerTestPayload::class, []);

        $this->assertInstanceOf(SerializerTestPayload::class, $payload);
        $this->assertEquals('test', $payload->body);
    }

    public function makeSerializer()
    {
        $this->config->shouldReceive('getHandlers')->andReturn([]);

        return new Serializer(
            new SerializerBuilder(),
            $this->config,
            'v1.0.0'
        );
    }
}

class SerializerTestPayload implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /** @JMS\Type("string") */
    public string $body;
}
