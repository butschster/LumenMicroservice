<?php

namespace Butschster\Tests\Exchange\Point;

use Butschster\Exchanger\Exceptions\RouteNotFoundException;
use Butschster\Exchanger\Exchange\IncomingRequest;
use Butschster\Exchanger\Exchange\Point\Argument;
use Butschster\Exchanger\Exchange\Point\Information;
use Butschster\Exchanger\Exchange\Point\Subject;
use Butschster\Tests\TestCase;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class InformationTest extends TestCase
{
    private Information $info;
    private Collection $subjects;
    private Collection $arguments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->info = new Information($this->subjects = new Collection([
            new Subject(
                $this->mockExchangePoint(),
                'methodName',
                'Test description',
                'com.test.action.name',
                 $this->arguments = new Collection([
                    new Argument('request', IncomingRequest::class),
                    new Argument('logger', LoggerInterface::class)
                ]),
                ['middleware1', 'middleware2', 'middleware3'],
                ['middleware3'],
            ),
            new Subject(
                $this->mockExchangePoint(),
                'methodName1',
                'Test description',
                'com.test.action.name1',
                $this->arguments = new Collection([
                    new Argument('request', IncomingRequest::class)
                ]),
                ['middleware2', 'middleware3'],
            )
        ]));
    }

    function test_gets_routes()
    {
        $this->assertEquals(
            $this->subjects,
            $this->info->getRoutes()
        );
    }

    function test_gets_route_subjects()
    {
        $this->assertEquals(
            ['com.test.action.name', 'com.test.action.name1'],
            $this->info->getRouteSubjects()
        );
    }

    function test_gets_route_by_subject_name()
    {
        $route = $this->info->getRoute('com.test.action.name');

        $this->assertInstanceOf(Subject::class, $route);
        $this->assertEquals('com.test.action.name', $route->getSubject());
    }

    function test_if_route_is_not_found_throw_an_exception()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route with subject [com.test.action.test] is not found.');

        $this->info->getRoute('com.test.action.test');
    }
}
