<?php

namespace Butschster\Tests\Exchange\Request;

use Butschster\Exchanger\Exchange\Request\MessageValidator;
use Butschster\Tests\TestCase;

class MessageValidatorTest extends TestCase
{
    /**
     * @dataProvider validationPayloads
     * @throws \Illuminate\Validation\ValidationException
     */
    function test_validate(string $body, array $payload)
    {
        $factory = $this->mock(\Illuminate\Contracts\Validation\Factory::class);

        $validator = new MessageValidator($factory);

        $message = $this->mockAmqpMessage();
        $message->shouldReceive('getBody')
            ->once()->andReturn($body);

        $factory->shouldReceive('make')
            ->once()
            ->with($payload, $rules = ['key' => 'required'])
            ->andReturn($v = $this->mock(\Illuminate\Contracts\Validation\Validator::class));

        $v->shouldReceive('validate')->once();

        $validator->validate($message, $rules);
    }

    public function validationPayloads()
    {
        return [
            ['{"payload":{"key":"value"}}', ['key' => 'value']],
            ['{}', []]
        ];
    }
}
