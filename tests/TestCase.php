<?php

namespace Butschster\Tests;

use Butschster\Exchanger\Contracts\ExchangeManager;
use Butschster\Exchanger\Exchange\Request\Dispatcher;
use Butschster\Exchanger\Jms\Config;
use Butschster\Exchanger\Payloads\Request;
use Butschster\Exchanger\Payloads\Response;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Mockery as m;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Contracts\Amqp;
use Psr\Log\LoggerInterface;
use Butschster\Exchanger\Exchange\Request\MessageValidator;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @return m\MockInterface|ConfigRepository
     */
    protected function mockConfigRepository(): m\MockInterface
    {
        return $this->mock(ConfigRepository::class);
    }

    /**
     * @return m\MockInterface|LoggerInterface
     */
    protected function mockLogger(): m\MockInterface
    {
        return $this->mock(LoggerInterface::class);
    }

    /**
     * @return m\MockInterface|Exchange\Request
     */
    protected function mockExchangeRequest(): m\MockInterface
    {
        return $this->mock(Exchange\Request::class);
    }

    /**
     * @return m\MockInterface|Exchange\Request
     */
    protected function mockExchangeRequestTokenDecoder(): m\MockInterface
    {
        return $this->mock(Exchange\Request\TokenDecoder::class);
    }

    /**
     * @return m\MockInterface|Exchange\Point
     */
    protected function mockExchangePoint(): m\MockInterface
    {
        return $this->mock(Exchange\Point::class);
    }

    /**
     * @return m\MockInterface|Serializer
     */
    protected function mockSerializer(): m\MockInterface
    {
        return $this->mock(Serializer::class);
    }

    /**
     * @return m\MockInterface|Serializer\Handler
     */
    protected function mockSerializerHandler(): m\MockInterface
    {
        return $this->mock(Serializer\Handler::class);
    }

    /**
     * @return m\MockInterface|Exchange\Client
     */
    protected function mockExchangeClient(): m\MockInterface
    {
        return $this->mock(Exchange\Client::class);
    }

    /**
     * @return m\MockInterface|Exchange\IncomingRequest
     */
    protected function mockExchangeIncomingRequest(): m\MockInterface
    {
        return $this->mock(Exchange\IncomingRequest::class);
    }

    /**
     * @return m\MockInterface|Exchange\Payload
     */
    protected function mockExchangePayload(): m\MockInterface
    {
        return $this->mock(Exchange\Payload::class);
    }

    /**
     * @return m\MockInterface|Exchange\PayloadFactory
     */
    protected function mockExchangePayloadFactory(): m\MockInterface
    {
        return $this->mock(Exchange\PayloadFactory::class);
    }

    /**
     * @return m\MockInterface|Exchange\Response
     */
    protected function mockExchangeResponse(): m\MockInterface
    {
        return $this->mock(Exchange\Response::class);
    }

    /**
     * @return m\MockInterface|Exchange\Route
     */
    protected function mockExchangeRoute(): m\MockInterface
    {
        return $this->mock(Exchange\Route::class);
    }

    /**
     * @return m\MockInterface|Exchange\Config
     */
    protected function mockExchangeConfig(): m\MockInterface
    {
        return $this->mock(Exchange\Config::class);
    }

    /**
     * @return m\MockInterface|Config
     */
    protected function mockSerializerConfig(): m\MockInterface
    {
        return $this->mock(Config::class);
    }

    /**
     * @return m\MockInterface|ExchangeManager
     */
    protected function mockExchangeManager(): m\MockInterface
    {
        return $this->mock(ExchangeManager::class);
    }

    /**
     * @return m\MockInterface|Container
     */
    protected function mockContainer(): m\MockInterface
    {
        return $this->mock(Container::class);
    }

    /**
     * @return m\MockInterface|Amqp\Connector
     */
    protected function mockAmqpConnector(): m\MockInterface
    {
        return $this->mock(Amqp\Connector::class);
    }

    /**
     * @return m\MockInterface|Amqp\Consumer
     */
    protected function mockAmqpConsumer(): m\MockInterface
    {
        return $this->mock(Amqp\Consumer::class);
    }

    /**
     * @return m\MockInterface|Amqp\Message
     */
    protected function mockAmqpMessage(): m\MockInterface
    {
        return $this->mock(Amqp\Message::class);
    }

    /**
     * @return m\MockInterface|Amqp\Publisher
     */
    protected function mockAmqpPublisher(): m\MockInterface
    {
        return $this->mock(Amqp\Publisher::class);
    }

    /**
     * @return m\MockInterface|Amqp\Requester
     */
    protected function mockAmqpRequester(): m\MockInterface
    {
        return $this->mock(Amqp\Requester::class);
    }

    /**
     * @return m\MockInterface|Request
     */
    protected function mockRequestPayload(): m\MockInterface
    {
        return $this->mock(Request::class);
    }

    /**
     * @return m\MockInterface|Request\Headers
     */
    protected function mockRequestHeadersPayload(): m\MockInterface
    {
        return $this->mock(Request\Headers::class);
    }

    /**
     * @return m\MockInterface|Response
     */
    protected function mockResponsePayload(): m\MockInterface
    {
        return $this->mock(Response::class);
    }

    /**
     * @return m\MockInterface|Response\Headers
     */
    protected function mockResponseHeadersPayload(): m\MockInterface
    {
        return $this->mock(Response\Headers::class);
    }

    /**
     * @return m\MockInterface|MessageValidator
     */
    protected function mockMessageValidator(): m\MockInterface
    {
        return $this->mock(MessageValidator::class);
    }

    protected function mockExchangeRequestDispatcher(): m\MockInterface
    {
        return $this->mock(Dispatcher::class);
    }

    /**
     * @param string $class
     * @return m\MockInterface
     */
    protected function mock(string $class): m\MockInterface
    {
        return m::mock($class);
    }
}
