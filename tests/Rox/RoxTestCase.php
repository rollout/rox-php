<?php

namespace Rox;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Logging\TestLoggerFactory;

abstract class RoxTestCase extends TestCase
{
    /**
     * @var TestLoggerFactory $_loggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var bool $_noWarningsExpected
     */
    private $_noWarningsExpected;

    /**
     * @var bool $_noErrorsExpected
     */
    private $_noErrorsExpected;

    protected function setUp(): void
    {
        parent::setUp();

        $this->_loggerFactory = new TestLoggerFactory();
        LoggerFactory::setup($this->_loggerFactory);

        $this->_noWarningsExpected = false;
        $this->_noErrorsExpected = false;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();

        $testLogger = $this->_loggerFactory->getLogger();
        if ($this->_noWarningsExpected) {
            $this->assertFalse($testLogger->hasWarningRecords(),
                "Expected no warnings but there are: " . $this->formatLogRecords(LogLevel::WARNING));
        }

        if ($this->_noErrorsExpected) {
            $this->assertFalse($testLogger->hasErrorRecords(),
                "Expected no errors but there are: " . $this->formatLogRecords(LogLevel::ERROR));
        }
    }

    protected function expectNoWarnings()
    {
        $this->_noWarningsExpected = true;
    }

    protected function expectNoErrors()
    {
        $this->_noErrorsExpected = true;
    }

    /**
     * @param string $level
     * @return string
     */
    private function formatLogRecords($level)
    {
        $testLogger = $this->_loggerFactory->getLogger();
        if (!array_key_exists($level, $testLogger->recordsByLevel)) {
            return '';
        }
        return join(", ", array_map(function ($record) {
            return sprintf("[%s] %s (%s)", $record['level'], $record['message'], json_encode($record['context']));
        }, $testLogger->recordsByLevel[$level]));
    }
}
