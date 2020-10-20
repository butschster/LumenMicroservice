<?php

namespace Butschster\Exchanger\Amqp;

use PhpAmqpLib\Message\AMQPMessage;
use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Contracts\Amqp\Publisher as PublisherContract;

/**
 * @internal
 */
class Publisher implements PublisherContract
{
    private ConnectorContract $connector;

    public function __construct(ConnectorContract $connector)
    {
        $this->connector = $connector;
    }

    public function publish(array $properties, string $route, string $message, bool $persistent = true): void
    {
        $properties['routing'] = $route;
        $properties['nobinding'] = true;

        $this->connector->connect($properties);

        $message = new AMQPMessage($message, [
            'content_type' => 'application/json',
            'delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
        ]);

        $this->connector->getChannel()->basic_publish(
            $message,
            $this->connector->getExchange(),
            $route
        );

        $this->connector->disconnect();
    }
}
