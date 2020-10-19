<?php

namespace Butschster\Tests\Amqp;

use Butschster\Exchanger\Amqp\Publisher;
use Butschster\Tests\TestCase;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class PublisherTest extends TestCase
{
    function test_publish()
    {
        $publisher = new Publisher(
            $connector = $this->mockAmqpConnector()
        );

        $properties = ['key' => 'value'];

        $connector->shouldReceive('connect')->once()->with($properties + ['routing' => 'com.test', 'nobinding' => true]);
        $connector->shouldReceive('getExchange')->once()->andReturn('exchange.test');
        $connector->shouldReceive('disconnect')->once();

        $connector->shouldReceive('getChannel')->once()->andReturn($channel = $this->mock(AMQPChannel::class));
        $channel->shouldReceive('basic_publish')->once()->withArgs(function (AMQPMessage $message, $exchange, $route) {
            return $message->getBody() === '{foo:bar}'
                && $message->get_properties() === [
                    'content_type' => 'application/json',
                    'delivery_mode' => 5,
                ]
                && $route === 'com.test'
                && $exchange === 'exchange.test';
        });

        $publisher->publish($properties, 'com.test', '{foo:bar}', 5);
    }
}
