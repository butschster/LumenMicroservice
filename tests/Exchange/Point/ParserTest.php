<?php

namespace Butschster\Tests\Exchange\Point;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Exchange\Point\Parser;
use Butschster\Tests\TestCase;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlockFactory;
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

        $this->assertCount(5, $information->getRouteSubjects());

        // Only public methods with subject annotations can be subjects
        $this->assertContains('com.test.action.name', $information->getRouteSubjects());
        $this->assertContains('com.test.action.name1', $information->getRouteSubjects());

        $route1 = $information->getRoute('com.test.action.name');
        $this->assertCount(0, $route1->getMiddleware());
        $this->assertCount(1, $route1->getDisabledMiddleware());
        $this->assertCount(2, $route1->getArguments());

        $this->assertEquals('Butschster\Tests\Exchange\Point\TestRequestPayload', $route1->getRequestPayload());
        $this->assertEquals('Butschster\Tests\Exchange\Point\TestResponsePayload', $route1->getResponsePayload());

        $route2 = $information->getRoute('com.test.action.name1');
        $this->assertCount(2, $route2->getMiddleware());
        $this->assertCount(0, $route2->getDisabledMiddleware());
        $this->assertCount(1, $route2->getArguments());
    }

    function test_if_given_request_payload_is_not_found_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Request payload [Butschster\Tests\Exchange\Point\NonExistsRequestPayload] is not found');

        $this->parser->parse(new TestPointWithNotFoundRequestPayload());
    }

    function test_if_given_response_payload_is_not_found_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Response payload [Butschster\Tests\Exchange\Point\NonExistsResponsePayload] is not found');

        $this->parser->parse(new TestPointWithNotFoundResponsePayload());
    }
}

class TestRequestPayload implements Payload
{

}

class TestResponsePayload implements Payload
{

}

class TestPoint implements Point
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * @subject com.test.action.name
     * @requestPayload Butschster\Tests\Exchange\Point\TestRequestPayload
     * @responsePayload Butschster\Tests\Exchange\Point\TestResponsePayload
     * @disableMiddleware Butschster\Tests\Exchange\Point\TestMiddleware1
     */
    public function methodWithSubject(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    /**
     * @subject com.test.action.name1
     * @middleware Butschster\Tests\Exchange\Point\TestMiddleware
     * @middleware Butschster\Tests\Exchange\Point\TestMiddleware1
     */
    public function methodWithSubjectAndMiddleware(IncomingRequest $request): void
    {

    }

    /**
     * @subject com.test.action.name5
     * @subject com.test.action.name6
     * @subject com.test.action.name7
     * @disableMiddleware Butschster\Tests\Exchange\Point\TestMiddleware1
     */
    public function methodWithMultiplySubjects(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    public function methodWithoutSubject(): void{}

    /**
     * @subject com.test.action.name2
     */
    protected function protectedMethod(): void{}

    /**
     * @subject com.test.action.name3
     */
    private function privateMethod(): void{}
}

class TestPointWithNotFoundRequestPayload implements Point
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * @subject com.test.action.name
     * @requestPayload Butschster\Tests\Exchange\Point\NonExistsRequestPayload
     */
    public function methodWithSubject(IncomingRequest $request, LoggerInterface $logger): void
    {

    }
}

class TestPointWithNotFoundResponsePayload implements Point
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * @subject com.test.action.name
     * @responsePayload Butschster\Tests\Exchange\Point\NonExistsResponsePayload
     */
    public function methodWithSubject(IncomingRequest $request, LoggerInterface $logger): void
    {

    }
}

class TestMiddleware {}
class TestMiddleware1 {}
