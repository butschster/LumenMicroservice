<?php

namespace Butschster\Tests\Exchange\Point;

use Butschster\Exchanger\Exchange\IncomingRequest;
use Butschster\Exchanger\Exchange\Point\Argument;
use Butschster\Exchanger\Exchange\Point\Subject;
use Butschster\Tests\TestCase;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class SubjectTest extends TestCase
{
    private Subject $subject;
    private Collection $arguments;
    /** @var \Butschster\Exchanger\Contracts\Exchange\Point|\Mockery\MockInterface */
    private $point;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Subject(
            $this->point = $this->mockExchangePoint(),
            'methodName',
            'Test description',
            'com.test.action.name',
            $this->arguments = new Collection([
                new Argument('request', IncomingRequest::class),
                new Argument('logger', LoggerInterface::class)
            ]),
            ['middleware1', 'middleware2', 'middleware3'],
            ['middleware3'],
            'Test\RequestPayload',
            'Test\ResponsePayload',
        );
    }

    function test_gets_subject()
    {
        $this->assertEquals(
            'com.test.action.name',
            $this->subject->getSubject()
        );
    }

    function test_gets_name()
    {
        $this->assertEquals(
            'methodName',
            $this->subject->getName()
        );
    }

    function test_gets_description()
    {
        $this->assertEquals(
            'Test description',
            $this->subject->getDescription()
        );
    }


    function test_gets_arguments()
    {
        $this->assertEquals(
            $this->arguments,
            $this->subject->getArguments()
        );
    }

    function test_gets_middleware()
    {
        $this->assertEquals(
            ['middleware1', 'middleware2'],
            $this->subject->getMiddleware()
        );
    }

    function test_gets_disabled_middleware()
    {
        $this->assertEquals(
            ['middleware3'],
            $this->subject->getDisabledMiddleware()
        );
    }

    function test_calls_exchange_point_method()
    {
        $dependencies = ['foo', 'bar'];
        $this->point->shouldReceive('methodName')
            ->once()->with('foo', 'bar');

        $this->subject->call($dependencies);
    }

    function test_gets_request_payload()
    {
        $this->assertEquals(
            'Test\RequestPayload',
            $this->subject->getRequestPayload()
        );
    }

    function test_gets_response_payload()
    {
        $this->assertEquals(
            'Test\ResponsePayload',
            $this->subject->getResponsePayload()
        );
    }

    function test_converts_to_array()
    {
        $this->assertEquals(
            [
                'name' => 'methodName',
                'description' => 'Test description',
                'middleware' => ['middleware1', 'middleware2'],
                'disabledMiddleware' => ['middleware3'],
                'request_payload' => 'Test\RequestPayload',
                'response_payload' => 'Test\ResponsePayload',
                'arguments' => [
                    [
                        'class' => 'Butschster\Exchanger\Exchange\IncomingRequest',
                        'name' => 'request'
                    ],
                    [
                        'class' => 'Psr\Log\LoggerInterface',
                        'name' => 'logger'
                    ]
                ]
            ],
            $this->subject->toArray()
        );
    }
}
