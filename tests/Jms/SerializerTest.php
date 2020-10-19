<?php

namespace Butschster\Tests\Jms;

use Butschster\Exchanger\Jms\Serializer;
use Butschster\Tests\TestCase;
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

        $this->config = $this->mockExchangeConfig();
    }

    function test_serialize()
    {
        $payload = new TestPayload();
        $payload->body = 'test';

        $json = $this->makeSerializer()->serialize($payload, []);
        $this->assertEquals('{"body":"test"}', $json);
    }

    function test_deserialize()
    {
        $payload = $this->makeSerializer()->deserialize('{"body":"test"}', TestPayload::class, []);

        $this->assertInstanceOf(TestPayload::class, $payload);
        $this->assertEquals('test', $payload->body);
    }

    public function makeSerializer()
    {
        $this->config->shouldReceive('version')->andReturn('1.0');

        return new Serializer(
            $this->config,
            new SerializerBuilder()
        );
    }
}

class TestPayload implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /** @JMS\Type("string") */
    public string $body;
}
