<?php

namespace Butschster\Tests\Exchange\Point;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Exchange\Point\Parser;
use Butschster\Tests\TestCase;
use Psr\Log\LoggerInterface;

class ParserTest extends TestCase
{
    private TestPoint $point;
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->point = new TestPoint();
        $this->parser = new Parser();
    }

    function test_parse()
    {
        $information = $this->parser->parse($this->point);

        $this->assertCount(2, $information->getRouteSubjects());

        // Only public methods with subject annotations can be subjects
        $this->assertContains('com.test.action.name', $information->getRouteSubjects());
        $this->assertContains('com.test.action.name1', $information->getRouteSubjects());

        $route1 = $information->getRoute('com.test.action.name');
        $this->assertCount(0, $route1->getMiddleware());
        $this->assertCount(1, $route1->getDisabledMiddleware());
        $this->assertCount(2, $route1->getArguments());

        $route2 = $information->getRoute('com.test.action.name1');
        $this->assertCount(2, $route2->getMiddleware());
        $this->assertCount(0, $route2->getDisabledMiddleware());
        $this->assertCount(1, $route2->getArguments());
    }
}

class TestPoint implements Point
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * @subject action.name
     * @disableMiddleware Butschster\Tests\Exchange\Point\TestMiddleware1
     */
    public function methodWithSubject(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    /**
     * @subject action.name1
     * @middleware Butschster\Tests\Exchange\Point\TestMiddleware
     * @middleware Butschster\Tests\Exchange\Point\TestMiddleware1
     */
    public function methodWithSubjectAndMiddleware(IncomingRequest $request): void
    {

    }

    public function methodWithoutSubject(): void{}

    /**
     * @subject action.name2
     */
    protected function protectedMethod(): void{}

    /**
     * @subject action.name3
     */
    private function privateMethod(): void{}
}

class TestMiddleware {}
class TestMiddleware1 {}
