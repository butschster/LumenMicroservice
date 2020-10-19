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
            'com.test.action.name',
            $this->arguments = new Collection([
                new Argument('request', IncomingRequest::class),
                new Argument('logger', LoggerInterface::class)
            ]),
            ['middleware1', 'middleware2', 'middleware3'],
            ['middleware3'],
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
}
