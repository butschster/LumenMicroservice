<?php

namespace Butschster\Tests\Exchange;

use Butschster\Exchanger\Exchange\Config;
use Butschster\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var \Illuminate\Contracts\Config\Repository|\Mockery\MockInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mockConfigRepository();
    }

    function test_gets_name()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('microservice.name')->andReturn('test');

        $this->assertEquals('test', $this->makeConfig()->name());
    }

    function test_gets_version()
    {
        $this->repository->shouldReceive('get')
            ->once()->with('microservice.version')->andReturn('1.0');

        $this->assertEquals('1.0', $this->makeConfig()->version());
    }

    protected function makeConfig()
    {
        return new Config($this->repository);
    }
}
