<?php

namespace Butschster\Exchanger\Amqp;

use PhpAmqpLib\Message\AMQPMessage;
use Butschster\Exchanger\Contracts\Amqp\Consumer as ConsumerContract;
use Butschster\Exchanger\Contracts\Amqp\Message as MessageContract;
use Butschster\Exchanger\Contracts\Exchange\Payload as PayloadContract;
use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Response;

/**
 * @internal
 */
class Message implements MessageContract
{
    private string $body = '';
    private string $subject;
    private ?string $correlationId = null;
    private ?string $replyTo = null;
    private ConsumerContract $consumer;
    private ?AMQPMessage $originalMessage = null;
    private Serializer $serializer;
    private PayloadFactory $factory;

    public function __construct(
        PayloadFactory $factory,
        Serializer $serializer,
        ConsumerContract $consumer,
        string $body,
        string $subject,
        ?string $correlationId = null,
        ?string $replyTo = null,
        ?AMQPMessage $originalMessage = null
    )
    {
        $this->originalMessage = $originalMessage;
        $this->serializer = $serializer;
        $this->body = $body;
        $this->subject = $subject;
        $this->correlationId = $correlationId;
        $this->replyTo = $replyTo;
        $this->consumer = $consumer;
        $this->factory = $factory;
    }

    /** @inheritDoc */
    public function getBody(): string
    {
        return $this->body;
    }

    /** @inheritDoc */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /** @inheritDoc */
    public function getPayload(string $property, ?string $class = null)
    {
        $body = json_decode($this->getBody());

        if (isset($body->{$property})) {
            if ($class) {
                return $this->serializer->deserialize(json_encode($body->{$property}), $class);
            }

            return $body->{$property};
        }

        return null;
    }

    /** @inheritDoc */
    public function reply(PayloadContract $payload, array $errors = [], ?Response\Headers $headers = null, bool $persistent = true): void
    {
        $responseMessage = $this->serializer->serialize(
            $this->factory->createResponse($payload, $errors, $headers),
            [Payload::class,]
        );

        $reply = new AMQPMessage($responseMessage, [
            'content_type' => 'application/json',
            'delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
            'correlation_id' => $this->correlationId,
        ]);

        $this->consumer->reply($reply, $this->replyTo);

        $this->acknowledge();
    }

    /** @inheritDoc */
    public function acknowledge(): void
    {
        if ($this->originalMessage) {
            $this->consumer->acknowledge($this->originalMessage);
        }
    }
}
