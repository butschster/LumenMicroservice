<?php

namespace Butschster\Tests;

use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\ExchangeManager;
use Butschster\Exchanger\Payloads\Error;
use Psr\Log\LoggerInterface;

class ExchangeManagerTest extends TestCase
{
    /** @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface */
    private $container;
    /** @var \Butschster\Exchanger\Contracts\Exchange\Config|\Mockery\MockInterface */
    private $config;
    /** @var \Butschster\Exchanger\Contracts\Exchange\Client|\Mockery\MockInterface */
    private $client;
    /** @var \Butschster\Exchanger\Contracts\Serializer|\Mockery\MockInterface */
    private $serializer;
    /** @var \Mockery\MockInterface|\Psr\Log\LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->mockContainer();
        $this->config = $this->mockExchangeConfig();
        $this->client = $this->mockExchangeClient();
        $this->serializer = $this->mockSerializer();
        $this->logger = $this->mockLogger();
    }

    function test_gets_name()
    {
        $this->assertEquals(
            'test',
            $this->makeExchangeManager()->getName()
        );
    }

    function test_gets_version()
    {
        $this->assertEquals(
            '1.0',
            $this->makeExchangeManager()->getVersion()
        );
    }

    function test_register_exchange_point()
    {
        $this->logger->shouldReceive('debug')->once();

        $point = $this->mockExchangePoint();
        $point->shouldReceive('getName')->once()->andReturn('com.test');

        $this->client->shouldReceive('subscribe')->once()
            ->withArgs(function ($p, callable $handler) use ($point) {
                return $p === $point;
            });

        $this->makeExchangeManager()->register($point);
    }

    function test_sends_request_without_payload()
    {
        $this->container->shouldReceive('make')->once()->with(PayloadFactory::class)
            ->andReturn($factory = $this->mockExchangePayloadFactory());

        $factory->shouldReceive('createRequest')
            ->once()->with(null)->andReturn($requestPayload = $this->mockRequestPayload());

        $request = $this->makeExchangeManager()->request('com.test');

        $this->assertEquals('com.test', $request->getSubject());
        $this->assertEquals($requestPayload, $request->getPayload());
    }

    function test_sends_request_with_payload()
    {
        $this->container->shouldReceive('make')->once()->with(PayloadFactory::class)
            ->andReturn($factory = $this->mockExchangePayloadFactory());

        $payload = new Error();
        $factory->shouldReceive('createRequest')->once()->with($payload)
            ->andReturn($requestPayload = $this->mockRequestPayload());

        $request = $this->makeExchangeManager()->request('com.test', $payload);

        $this->assertEquals('com.test', $request->getSubject());
        $this->assertEquals($requestPayload, $request->getPayload());
    }

    function test_broadcasts_data_without_payload()
    {
        $this->container->shouldReceive('make')->once()->with(PayloadFactory::class)
            ->andReturn($factory = $this->mockExchangePayloadFactory());

        $factory->shouldReceive('createRequest')
            ->once()->with(null)->andReturn($requestPayload = $this->mockRequestPayload());

        $this->serializer->shouldReceive('serialize')
            ->once()->with($requestPayload)->andReturn($serializedData = '{hello: world}');
        $this->client->shouldReceive('broadcast')->once()->with('com.test', $serializedData);

        $this->assertNull(
            $this->makeExchangeManager()->broadcast('com.test')
        );
    }

    function test_broadcasts_data_with_payload()
    {
        $this->container->shouldReceive('make')->once()->with(PayloadFactory::class)
            ->andReturn($factory = $this->mockExchangePayloadFactory());

        $payload = new Error();
        $factory->shouldReceive('createRequest')->once()
            ->with($payload)->andReturn($requestPayload = $this->mockRequestPayload());

        $this->serializer->shouldReceive('serialize')
            ->once()->with($requestPayload)->andReturn($serializedData = '{hello: world}');
        $this->client->shouldReceive('broadcast')->once()->with('com.test', $serializedData);

        $this->assertNull(
            $this->makeExchangeManager()->broadcast('com.test', $payload)
        );
    }

    function test_sets_logger()
    {
        $logger = $this->mockLogger();
        $this->container->shouldReceive('bind')->once()
            ->withArgs(function ($class, callable $closure) use ($logger) {
                return $class === LoggerInterface::class &&
                    $closure() === $logger;
            });

        $manager = $this->makeExchangeManager();


        $this->assertNull(
            $manager->setLogger($logger)
        );

        $this->assertEquals($logger, $manager->getLogger());
    }

    public function makeExchangeManager()
    {
        $this->config->shouldReceive('name')->once()->andReturn('test');
        $this->config->shouldReceive('version')->once()->andReturn('1.0');

        return new ExchangeManager(
            $this->container,
            $this->config,
            $this->client,
            $this->serializer,
            $this->logger
        );
    }
}
