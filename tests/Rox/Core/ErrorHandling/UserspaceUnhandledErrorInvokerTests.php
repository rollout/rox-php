<?php

namespace Rox\Core\ErrorHandling;

use Exception;
use Mockery;
use Psr\Log\LoggerInterface;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Logging\LoggerFactoryInterface;
use Rox\RoxTestCase;

class UserspaceUnhandledErrorInvokerTests extends RoxTestCase
{
    public function testWillWriteErrorWhenInvokeUserUnhandledErrorInvokeHandlerWasNotSet()
    {
        $exception = new Exception("some exception");
        $obj = "123";
        $msg = null;
        $exThrown = null;

        $log = Mockery::mock(LoggerInterface::class)
            ->shouldReceive('error')
            ->andReturnUsing(function ($message, $context) use (&$msg, &$exThrown) {
                echo 'MSG=' . $message;
                $msg = $message;
                $exThrown = $context['exception'];
            })
            ->getMock();

        $loggerFactory = Mockery::mock(LoggerFactoryInterface::class)
            ->shouldReceive('createLogger')
            ->andReturn($log)
            ->byDefault()
            ->getMock();

        LoggerFactory::setup($loggerFactory);

        $userUnhandledErrorInvoker = new UserspaceUnhandledErrorInvoker();
        $userUnhandledErrorInvoker->invoke($obj, ExceptionTrigger::ConfigurationFetchedHandler, $exception);

        $this->assertStringStartsWith("User Unhandled Error occurred", $msg);
        $this->assertSame($exception, $exThrown);
    }

    public function testWillWriteErrorWhenInvokeUserUnhandledErrorInvokerThrewException()
    {
        $exception = new Exception("some exception");
        $exHandlerException = new Exception("userUnhandledError exception");
        $obj = "123";

        $msg = null;
        $exThrown = null;

        $log = Mockery::mock(LoggerInterface::class)
            ->shouldReceive('error')
            ->andReturnUsing(function ($message, $context) use (&$msg, &$exThrown) {
                echo 'MSG=' . $message;
                $msg = $message;
                $exThrown = $context['exception'];
            })
            ->getMock();

        $loggerFactory = Mockery::mock(LoggerFactoryInterface::class)
            ->shouldReceive('createLogger')
            ->andReturn($log)
            ->byDefault()
            ->getMock();

        LoggerFactory::setup($loggerFactory);

        $handler = function (UserspaceUnhandledErrorArgs $uueArgs) use ($exHandlerException) {
            throw $exHandlerException;
        };

        $userUnhandledErrorInvoker = new UserspaceUnhandledErrorInvoker();
        $userUnhandledErrorInvoker->register($handler);
        $userUnhandledErrorInvoker->invoke($obj, ExceptionTrigger::ConfigurationFetchedHandler, $exception);

        $this->assertStringStartsWith("User Unhandled Error Handler itself", $msg);
        $this->assertSame($exception, $exThrown);
    }

    public function testWillInvokeUserUnhandledErrorInvoker()
    {
        $userUnhandledErrorInvoker = new UserspaceUnhandledErrorInvoker();
        $ex = new Exception("some exception");
        $exTrigger = ExceptionTrigger::DynamicPropertiesRule;
        $obj = "123";
        $invoked = 0;

        $handler = function (UserspaceUnhandledErrorArgs $uueArgs) use ($obj, &$invoked, $exTrigger, $ex) {
            $invoked++;
            $this->assertSame($obj, $uueArgs->getExceptionSource());
            $this->assertSame($exTrigger, $uueArgs->getExceptionTrigger());
            $this->assertSame($ex, $uueArgs->getException());
        };

        $userUnhandledErrorInvoker->register($handler);
        $userUnhandledErrorInvoker->invoke($obj, $exTrigger, $ex);

        $this->assertEquals(1, $invoked);
    }
}