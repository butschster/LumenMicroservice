<?php

namespace Butschster\Tests\Exchange\Point;

use Butschster\Exchanger\Exchange\Point\Argument;
use Butschster\Tests\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

class ArgumentTest extends TestCase
{
    private Argument $argument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->argument = new Argument(
            'logger',
            LoggerInterface::class
        );
    }

    function test_gets_name()
    {
        $this->assertEquals(
            'logger',
            $this->argument->getName()
        );
    }

    function test_gets_class()
    {
        $this->assertEquals(
            LoggerInterface::class,
            $this->argument->getClass()
        );
    }

    function test_is_with_exists_class()
    {
        $this->assertTrue(
            $this->argument->is(LoggerInterface::class, ConsoleLogger::class)
        );
    }

    function test_is_without_exists_class()
    {
        $this->assertFalse(
            $this->argument->is(ConsoleLogger::class)
        );
    }

    function test_it_can_be_converted_to_array()
    {
        $this->assertEquals(
            [
                'name' => 'logger',
                'class' => 'Psr\Log\LoggerInterface'
            ],
            $this->argument->toArray()
        );
    }
}
