<?php

namespace Butschster\Tests\Amqp;

use Butschster\Exchanger\Amqp\Message;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Request\Headers;
use Butschster\Exchanger\Payloads\Response;
use Butschster\Tests\TestCase;
use PhpAmqpLib\Message\AMQPMessage;

class MessageTest extends TestCase
{
    /** @var \Butschster\Exchanger\Contracts\Amqp\Consumer|\Mockery\MockInterface */
    private $consumer;
    /** @var \Butschster\Exchanger\Contracts\Exchange\PayloadFactory|\Mockery\MockInterface */
    private $factory;
    /** @var \Butschster\Exchanger\Contracts\Serializer|\Mockery\MockInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = $this->mockAmqpConsumer();
        $this->factory = $this->mockExchangePayloadFactory();
        $this->serializer = $this->mockSerializer();
    }

    function test_gets_body()
    {
        $this->assertEquals(
            '{"foo":"bar"}',
            $this->makeMessage()->getBody()
        );
    }

    function test_gets_subject()
    {
        $this->assertEquals(
            'com.test',
            $this->makeMessage()->getSubject()
        );
    }

    function test_gets_payload()
    {
        $payload = $this->makeMessage()->getPayload('foo');

        $this->assertEquals(
            'bar',
            $payload
        );
    }

    function test_gets_non_exists_payload()
    {
        $payload = $this->makeMessage()->getPayload('test');

        $this->assertNull(
            $payload
        );
    }

    function test_gets_payload_with_known_class()
    {
        $this->serializer->shouldReceive('deserialize')
            ->once()->with('"bar"', Headers::class)->andReturn('test');
        $payload = $this->makeMessage()->getPayload('foo', Headers::class);

        $this->assertEquals(
            'test',
            $payload
        );
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_replies_to_without_original_message(bool $persistent)
    {
        $response = new Response();
        $payload = new Payload();
        $errors = [new Error()];
        $headers = new Response\Headers();

        $this->factory->shouldReceive('createResponse')
            ->once()->with($payload, $errors, $headers)->andReturn($response);
        $this->serializer->shouldReceive('serialize')
            ->once()->with($response, [Payload::class])->andReturn($body = '{foo:bar}');
        $this->consumer->shouldReceive('reply')->once()->withArgs(function (AMQPMessage $reply, $replyTo) use ($body, $persistent) {
            return $reply->getBody() === $body
                && $reply->get_properties() === [
                    'content_type' => 'application/json',
                    'delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                    'correlation_id' => 'cor_id',
                ]
                && $replyTo === 'com.reply_to';
        });

        $this->makeMessage()->reply($payload, $errors, $headers, $persistent);
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_replies_to_with_original_message(bool $persistent)
    {
        $original = $this->mock(AMQPMessage::class);
        $response = new Response();
        $payload = new Payload();
        $errors = [new Error()];
        $headers = new Response\Headers();

        $this->factory->shouldReceive('createResponse')
            ->once()->with($payload, $errors, $headers)->andReturn($response);
        $this->serializer->shouldReceive('serialize')
            ->once()->with($response, [Payload::class])->andReturn($body = '{foo:bar}');
        $this->consumer->shouldReceive('reply')->once()->withArgs(function (AMQPMessage $reply, $replyTo) use ($body, $persistent) {
            return $reply->getBody() === $body
                && $reply->get_properties() === [
                    'content_type' => 'application/json',
                    'delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                    'correlation_id' => 'cor_id',
                ]
                && $replyTo === 'com.reply_to';
        });
        $this->consumer->shouldReceive('acknowledge')->once()->with($original);

        $this->makeMessage($original)->reply($payload, $errors, $headers);
    }

    public function makeMessage(?AMQPMessage $originalMessage = null): Message
    {
        return new Message(
            $this->factory,
            $this->serializer,
            $this->consumer,
            '{"foo":"bar"}',
            'com.test',
            'cor_id',
            'com.reply_to',
            $originalMessage
        );
    }

    public function persistenceDataProvider()
    {
        return [
            [true, false]
        ];
    }
}
