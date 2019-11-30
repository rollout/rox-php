<?php

namespace Rox;

use PHPUnit\Framework\TestCase;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Logging\TestLoggerFactory;

abstract class RoxTestCase extends TestCase
{
    /**
     * @var TestLoggerFactory $_loggerFactory
     */
    protected $_loggerFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->_loggerFactory = new TestLoggerFactory();
        LoggerFactory::setup($this->_loggerFactory);
    }

    protected function tearDown()
    {
        parent::tearDown();

        \Mockery::close();
    }
}