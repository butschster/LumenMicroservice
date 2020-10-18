<?php

namespace Butschster\Exchanger\Exchange\Request;

use Illuminate\Contracts\Validation\Factory;
use Butschster\Exchanger\Contracts\Amqp\Message;

class MessageValidator
{
    private Factory $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Validate incoming message payload parameters
     * @param Message $message
     * @param array $rules
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Message $message, array $rules): void
    {
        $data = json_decode($message->getBody(), true);

        $validator = $this->factory->make(
            (array)($data['payload'] ?? []),
            $rules
        );

        $validator->validate();
    }
}
