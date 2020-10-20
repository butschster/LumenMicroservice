<?php

namespace Butschster\Tests\Amqp;

use Butschster\Exchanger\Amqp\Publisher;
use Butschster\Tests\TestCase;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class PublisherTest extends TestCase
{
    /**
     * @dataProvider persistenceDataProvider
     */
    function test_publish(bool $persistent)
    {
        $publisher = new Publisher(
            $connector = $this->mockAmqpConnector()
        );

        $properties = ['key' => 'value'];

        $connector->shouldReceive('connect')->once()->with($properties + ['routing' => 'com.test', 'nobinding' => true]);
        $connector->shouldReceive('getExchange')->once()->andReturn('exchange.test');
        $connector->shouldReceive('disconnect')->once();

        $connector->shouldReceive('getChannel')->once()->andReturn($channel = $this->mock(AMQPChannel::class));
        $channel->shouldReceive('basic_publish')->once()->withArgs(function (AMQPMessage $message, $exchange, $route) use($persistent) {
            return $message->getBody() === '{foo:bar}'
                && $message->get_properties() === [
                    'content_type' => 'application/json',
                    'delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                ]
                && $route === 'com.test'
                && $exchange === 'exchange.test';
        });

        $publisher->publish($properties, 'com.test', '{foo:bar}', $persistent);
    }

    public function persistenceDataProvider()
    {
        return [
            [true, false]
        ];
    }
}
