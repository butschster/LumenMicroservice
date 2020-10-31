<?php

namespace Butschster\Exchanger\Providers;

use Butschster\Exchanger\Exchange\Config as ExchangeConfig;
use Butschster\Exchanger\Exchange\IncomingRequest;
use Butschster\Exchanger\Exchange\Request\JWTTokenDecoder;
use Butschster\Exchanger\Jms\Mapping;
use Butschster\Exchanger\Jms\ObjectsMapper;
use Doctrine\Common\Annotations\Reader;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use JMS\Serializer\Builder\CallbackDriverFactory;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Type\Parser;
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
use Butschster\Exchanger\Jms\Config as SerializerConfig;

class ExchangeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerAmqp();
        $this->registerSerializer();
        $this->registerExchangeManager();
        $this->registerPayloadFactory();
        $this->registerTokenDecoder();
    }

    private function registerExchangeManager()
    {
        $this->app->singleton(Exchange\Config::class, ExchangeConfig::class);
        $this->app->singleton(Exchange\Client::class, AmqpExchangeClient::class);
        $this->app->singleton(ExchangeManagerContract::class, ExchangeManager::class);
        $this->app->singleton(Exchange\IncomingRequest::class, IncomingRequest::class);
    }

    private function registerSerializer()
    {
        $this->app->singleton(SerializerContract::class, function() {
            $builder = new SerializerBuilder();

            return new Serializer(
                $builder,
                $this->app[SerializerConfig::class],
                $this->app[Exchange\Config::class]->version()
            );
        });

        $this->app->singleton(SerializerContract\ObjectsMapper::class, function () {
            $config = $this->app[SerializerConfig::class];

            return new ObjectsMapper(
                new CallbackDriverFactory(function (array $metadataDirs, Reader $annotationReader) use($config) {
                    return new Mapping\Driver(
                        $config,
                        new Parser()
                    );
                }),
                $this->app[SerializerContract::class],
                $config
            );
        });
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

    private function registerTokenDecoder()
    {
        $this->app->singleton(Exchange\Request\TokenDecoder::class, function () {
            $config = $this->app[Repository::class]->get('microservice.jwt');

            return new JWTTokenDecoder($config['secret'], $config['algo']);
        });
    }
}
