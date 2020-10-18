<?php

namespace Butschster\Exchanger\Providers;

use Butschster\Exchanger\Jms\MappingDriver;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use Butschster\Exchanger\Amqp\Config;
use Butschster\Exchanger\Amqp\Connector;
use Butschster\Exchanger\Amqp\Consumer;
use Butschster\Exchanger\Amqp\Client as AmqpExchangeClient;
use Butschster\Exchanger\Amqp\Publisher;
use Butschster\Exchanger\Amqp\Requester;
use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Contracts\Amqp\Consumer as ConsumerContract;
use Butschster\Exchanger\Contracts\Amqp\Publisher as PublisherContract;
use Butschster\Exchanger\Contracts\Amqp\Requester as RequesterContract;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Contracts\ExchangeManager as ExchangeManagerContract;
use Butschster\Exchanger\Contracts\Serializer as SerializerContract;
use Butschster\Exchanger\Exchange\DefaultPayloadFactory;
use Butschster\Exchanger\ExchangeManager;
use Butschster\Exchanger\Jms\Serializer;

class ExchangeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerAmqp();
        $this->registerSerializer();
        $this->registerExchangeManager();
        $this->registerPayloadFactory();
        $this->registerMappingDriver();
    }

    private function registerExchangeManager()
    {
        $this->app->singleton(Exchange\Client::class, AmqpExchangeClient::class);
        $this->app->singleton(ExchangeManagerContract::class, ExchangeManager::class);
    }

    private function registerSerializer()
    {
        $this->app->singleton(SerializerContract::class, Serializer::class);
    }

    private function registerAmqp()
    {
        $this->app->singleton(ConnectorContract::class, function () {
            $config = $this->app[Config::class];

            return new Connector(
                $config,
                new AMQPSSLConnection(
                    $config->getProperty('host'),
                    $config->getProperty('port'),
                    $config->getProperty('username'),
                    $config->getProperty('password'),
                    $config->getProperty('vhost'),
                    $config->getProperty('ssl_options'),
                    $config->getProperty('connect_options')
                )
            );
        });

        $this->app->singleton(ConsumerContract::class, Consumer::class);
        $this->app->singleton(RequesterContract::class, Requester::class);
        $this->app->singleton(PublisherContract::class, Publisher::class);
    }

    private function registerPayloadFactory()
    {
        $this->app->singleton(Exchange\PayloadFactory::class, DefaultPayloadFactory::class);
    }

    private function registerMappingDriver()
    {
        $this->app->singleton(\Metadata\Driver\AdvancedDriverInterface::class, MappingDriver::class);
    }
}
