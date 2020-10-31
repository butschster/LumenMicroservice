<?php

namespace Butschster\Tests\Providers;

use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Contracts\Amqp\Consumer as ConsumerContract;
use Butschster\Exchanger\Contracts\Amqp\Publisher as PublisherContract;
use Butschster\Exchanger\Contracts\Amqp\Requester as RequesterContract;
use Butschster\Exchanger\Contracts\ExchangeManager as ExchangeManagerContract;
use Butschster\Exchanger\Contracts\Serializer as SerializerContract;
use Butschster\Exchanger\Providers\ExchangeServiceProvider;
use Butschster\Tests\TestCase;
use Butschster\Exchanger\Contracts\Exchange;

class ExchangeServiceProviderTest extends TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface
     */
    private $container;
    private ExchangeServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->mockContainer();
        $this->provider = new ExchangeServiceProvider($this->container);
    }

    function test_register()
    {
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(Exchange\Config::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(Exchange\Client::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(ExchangeManagerContract::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(Exchange\IncomingRequest::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(Exchange\PayloadFactory::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(Exchange\Request\TokenDecoder::class);

        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(SerializerContract::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(SerializerContract\ObjectsMapper::class);

        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(ConnectorContract::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(ConsumerContract::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(RequesterContract::class);
        $this->container->shouldReceive('singleton')->once()->withSomeOfArgs(PublisherContract::class);

        $this->provider->register();
    }
}
