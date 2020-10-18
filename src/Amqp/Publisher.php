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

    public function publish(array $properties, string $route, string $message): void
    {
        $properties['routing'] = $route;
        $properties['nobinding'] = true;

        $this->connector->connect($properties);

        $message = new AMQPMessage($message, [
            'content_type' => 'text/json',
            'delivery_mode' => 2,
        ]);

        $this->connector->getChannel()->basic_publish(
            $message,
            $this->connector->getExchange(),
            $route
        );

        $this->connector->disconnect();
    }
}
