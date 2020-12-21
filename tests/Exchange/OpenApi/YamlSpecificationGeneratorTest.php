<?php

namespace Butschster\Tests\Exchange\OpenApi;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Exchange\Point\Parser;
use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;
use Butschster\Exchanger\OpenApi\YamlSpecificationGenerator;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Tests\TestCase;
use DateTime;
use OpenApi\Serializer;
use Psr\Log\LoggerInterface;
use JMS\Serializer\Annotation as JMS;

class YamlSpecificationGeneratorTest extends TestCase
{
    function test_generates()
    {
        $generator = new YamlSpecificationGenerator(
            new Parser(),
            new Serializer(),
            $exchanger = $this->mockExchangeManager(),
            new SchemaFactory()
        );

        $exchanger->shouldReceive('getVersion')->once()->andReturn('1.0.0');

        $this->assertEquals(
            "openapi: 3.0.0
info:
  title: com.test
  version: 1.0.0
servers: []
paths:
  /com.test.action.name:
    get:
      summary: 'Test description'
      parameters:
        -
          name: body
          in: query
          required: true
          schema:
            properties:
              headers:
                \$ref: '#/components/schemas/RequestHeaders'
              payload:
                \$ref: '#/components/schemas/TestRequestPayload'
            type: object
      responses:
        default:
          description: 'Something went wrong'
          content:
            application/json:
              schema:
                \$ref: '#/components/schemas/Error'
        '200':
          description: Success
          content:
            application/json:
              schema:
                properties:
                  success: { description: 'Response state', type: boolean }
                  headers: { \$ref: '#/components/schemas/ResponseHeaders' }
                  payload: { \$ref: '#/components/schemas/TestResponsePayload' }
                type: object
  /com.test.action.name1:
    get:
      summary: ''
      parameters:
        -
          name: body
          in: query
          required: true
          schema:
            properties:
              headers:
                \$ref: '#/components/schemas/RequestHeaders'
            type: object
      responses:
        default:
          description: 'Something went wrong'
          content:
            application/json:
              schema:
                \$ref: '#/components/schemas/Error'
        '200':
          description: Success
          content:
            application/json:
              schema:
                properties:
                  success: { description: 'Response state', type: boolean }
                  headers: { \$ref: '#/components/schemas/ResponseHeaders' }
                  payload: { \$ref: '#/components/schemas/TestResponsePayload' }
                type: object
  /com.test.action.name2:
    get:
      summary: ''
      parameters:
        -
          name: body
          in: query
          required: true
          schema:
            properties:
              headers:
                \$ref: '#/components/schemas/RequestHeaders'
              payload:
                \$ref: '#/components/schemas/TestRequestPayload'
            type: object
      responses:
        default:
          description: 'Something went wrong'
          content:
            application/json:
              schema:
                \$ref: '#/components/schemas/Error'
  /com.test.action.name3:
    get:
      summary: ''
      parameters:
        -
          name: body
          in: query
          required: true
          schema:
            properties:
              headers:
                \$ref: '#/components/schemas/RequestHeaders'
            type: object
      responses:
        default:
          description: 'Something went wrong'
          content:
            application/json:
              schema:
                \$ref: '#/components/schemas/Error'
components:
  schemas:
    Pagination:
      properties:
        total:
          description: ''
          type: integer
        perPage:
          description: ''
          type: integer
        currentPage:
          description: ''
          type: integer
        totalPages:
          description: ''
          type: integer
      type: object
    Meta:
      properties:
        pagination:
          \$ref: '#/components/schemas/Pagination'
      type: object
    RequestHeaders:
      properties:
        ip:
          description: ''
          type: string
        version:
          description: ''
          type: string
        requester:
          description: 'Service name that sent this request'
          type: string
        timestamp:
          description: ''
          type: string
          format: date-time
        meta:
          \$ref: '#/components/schemas/Meta'
        token:
          description: ''
          type: string
        tokenInfo:
          description: ''
          type: string
      type: object
    ResponseHeaders:
      properties:
        meta:
          \$ref: '#/components/schemas/Meta'
      type: object
    Trace:
      properties:
        file:
          description: ''
          type: string
        line:
          description: ''
          type: integer
        function:
          description: ''
          type: string
        class:
          description: ''
          type: string
      type: object
    Error:
      properties:
        code:
          description: ''
          type: integer
        message:
          description: ''
          type: string
        data:
          description: ''
          type: array
          items:
            type: string
        trace:
          description: ''
          type: array
          items:
            \$ref: '#/components/schemas/Trace'
      type: object
    Headers:
      properties:
        meta:
          \$ref: '#/components/schemas/Meta'
      type: object
    Payload:
      properties: {  }
      type: object
    Response:
      properties:
        success:
          description: ''
          type: boolean
        headers:
          \$ref: '#/components/schemas/Headers'
        payload:
          \$ref: '#/components/schemas/Payload'
        errors:
          description: ''
          type: array
          items:
            \$ref: '#/components/schemas/Error'
      type: object
    TestRequestPayload:
      properties:
        ip:
          description: 'Ip Address'
          type: string
        version:
          description: 'API Version'
          type: string
        error:
          \$ref: '#/components/schemas/Error'
        dateTime:
          description: 'API Version'
          type: string
          format: date-time
        array:
          description: 'Array without element types'
          type: array
          items:
            type: string
        arrayWithString:
          description: 'Array with string element types'
          type: array
          items:
            type: string
        arrayWithInteger:
          description: 'Array with integer element types'
          type: array
          items:
            type: integer
        responses:
          description: 'Array with objects'
          type: array
          items:
            \$ref: '#/components/schemas/Response'
        startAt:
          description: ''
          type: string
          format: date-time
        endAt:
          description: ''
          type: string
          format: date-time
        publishedAt:
          description: ''
          type: string
          format: date-time
        createdAt:
          description: ''
          type: string
          format: date-time
        updatedAt:
          description: ''
          type: string
          format: date-time
        deletedAt:
          description: ''
          type: string
          format: date-time
        published:
          description: ''
          type: boolean
        comments:
          description: ''
          type: array
          items:
            \$ref: '#/components/schemas/Error'
      type: object
    TestResponsePayload:
      properties:
        uuid:
          description: 'User ID'
          type: string
        username:
          description: Username
          type: string
      type: object
",
            $generator->generate(new TestPoint())
        );
    }
}

class TestRequestPayload implements Payload
{
    /**
     * Ip Address
     * @JMS\Type("string")
     */
    public ?string $ip = null;

    /**
     * API Version
     * @JMS\Type("string")
     */
    public ?string $version = null;

    /**
     * Error object
     * @JMS\Type("Butschster\Exchanger\Payloads\Error")
     */
    public Error $error;

    /**
     * API Version
     * @JMS\Type("DateTime<'Y-m-d', '', ['Y-m-d', 'Y/m/d']>")
     */
    public DateTime $dateTime;

    /**
     * Array without element types
     * @JMS\Type("array")
     */
    public array $array = [];

    /**
     * Array with string element types
     * @JMS\Type("array<string>")
     */
    public array $arrayWithString = [];

    /**
     * Array with integer element types
     * @JMS\Type("array<integer>")
     */
    public array $arrayWithInteger = [];

    /**
     * Array with objects
     * @JMS\Type("array<Butschster\Exchanger\Payloads\Response>")
     */
    public array $responses = [];

    /**
     * @JMS\Type("DateTime")
     */
    public $startAt;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public $endAt;

    /**
     * @JMS\Type("DateTime<'Y-m-d', '', ['Y-m-d', 'Y/m/d']>")
     */
    public $publishedAt;

    /**
     * @JMS\Type("DateTimeImmutable")
     */
    public $createdAt;

    /**
     * @JMS\Type("DateTimeImmutable<'Y-m-d'>")
     */
    public $updatedAt;

    /**
     * @JMS\Type("DateTimeImmutable<'Y-m-d', '', ['Y-m-d', 'Y/m/d']>")
     */
    public $deletedAt;

    /**
     * @JMS\Type("boolean")
     */
    public bool $published;

    /**
     * @JMS\Type("ArrayCollection<Butschster\Exchanger\Payloads\Error>")
     */
    public $comments;
}

class TestResponsePayload implements Payload
{
    /**
     * User ID
     * @JMS\Type("string")
     */
    public string $uuid;

    /**
     * Username
     * @JMS\Type("string")
     */
    public string $username;
}

class TestPoint implements Point
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * Test description
     * @subject com.test.action.name
     * @requestPayload Butschster\Tests\Exchange\OpenApi\TestRequestPayload
     * @responsePayload Butschster\Tests\Exchange\OpenApi\TestResponsePayload
     */
    public function methodWithSubject(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    /**
     * @subject com.test.action.name1
     * @responsePayload Butschster\Tests\Exchange\OpenApi\TestResponsePayload
     */
    public function methodWithoutRequestPayload(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    /**
     * @subject com.test.action.name2
     * @requestPayload Butschster\Tests\Exchange\OpenApi\TestRequestPayload
     */
    public function methodWithoutResponsePayload(IncomingRequest $request, LoggerInterface $logger): void
    {

    }

    /**
     * @subject com.test.action.name3
     */
    public function methodWithoutPayload(IncomingRequest $request, LoggerInterface $logger): void
    {

    }
}
