<?php

namespace Rox\Core\Configuration;

use Exception;
use Mockery;
use Rox\Core\ErrorHandling\ExceptionTrigger;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Utils\TimeUtils;
use Rox\RoxTestCase;

class ConfigurationFetchedInvokerTests extends RoxTestCase
{
    public function testConfigurationInvokerWithNoSubscriberNoException()
    {
        $configurationFetchedInvoker = new ConfigurationFetchedInvoker(
            Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $configurationFetchedInvoker->invokeWithError(FetcherError::Unknown);

        $configurationFetchedInvoker2 = new ConfigurationFetchedInvoker(
            Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $configurationFetchedInvoker2->invoke(FetcherStatus::AppliedFromEmbedded,
            TimeUtils::currentTimeMillis(), true);

        self::assertTrue(true);
    }

    public function testConfigurationFetchedArgsConstructor()
    {
        $this->expectNoWarnings();
        $this->expectNoErrors();

        $status = FetcherStatus::AppliedFromEmbedded;
        $time = TimeUtils::currentTimeMillis();
        $hasChanges = true;

        $args = new ConfigurationFetchedArgs(FetcherError::NoError, $status, $time, $hasChanges);

        $this->assertEquals($status, $args->getFetcherStatus());
        $this->assertEquals($time, $args->getCreationDate());
        $this->assertEquals($hasChanges, $args->isHasChanges());
        $this->assertEquals(FetcherError::NoError, $args->getErrorDetails());

        $error = FetcherError::SignatureVerificationError;
        $args2 = new ConfigurationFetchedArgs($error);

        $this->assertEquals(FetcherStatus::ErrorFetchedFailed, $args2->getFetcherStatus());
        $this->assertNull($args2->getCreationDate());
        $this->assertFalse(false, $args2->isHasChanges());
        $this->assertEquals(FetcherError::SignatureVerificationError, $args2->getErrorDetails());
    }

    public function testNoPushConfigurationInvokerInvokeWithError()
    {
        $this->expectNoWarnings();
        $this->expectNoErrors();

        $isConfigurationHandlerInvokerRaised = [false];
        $configurationFetchedInvoker = new ConfigurationFetchedInvoker(
            Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));

        $configurationFetchedInvoker->register(function (ConfigurationFetchedArgs $e)
        use ($configurationFetchedInvoker, &$isConfigurationHandlerInvokerRaised) {

            $this->assertEquals(FetcherStatus::ErrorFetchedFailed, $e->getFetcherStatus());
            $this->assertNull($e->getCreationDate());
            $this->assertFalse(false, $e->isHasChanges());
            $this->assertEquals(FetcherError::Unknown, $e->getErrorDetails());

            $isConfigurationHandlerInvokerRaised[0] = true;
        });

        $configurationFetchedInvoker->invokeWithError(FetcherError::Unknown);

        $this->assertTrue($isConfigurationHandlerInvokerRaised[0]);
    }

    public function testConfigurationInvokerInvokeWithError()
    {
        $this->expectNoWarnings();
        $this->expectNoErrors();

        $isConfigurationHandlerInvokerRaised = [false];
        $configurationFetchedInvoker = new ConfigurationFetchedInvoker(
            Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));

        $configurationFetchedInvoker->register(function (ConfigurationFetchedArgs $e)
        use ($configurationFetchedInvoker, &$isConfigurationHandlerInvokerRaised) {

            $this->assertEquals(FetcherStatus::ErrorFetchedFailed, $e->getFetcherStatus());
            $this->assertNull($e->getCreationDate());
            $this->assertFalse(false, $e->isHasChanges());
            $this->assertEquals(FetcherError::Unknown, $e->getErrorDetails());

            $isConfigurationHandlerInvokerRaised[0] = true;
        });

        $configurationFetchedInvoker->invokeWithError(FetcherError::Unknown);

        $this->assertTrue($isConfigurationHandlerInvokerRaised[0]);
    }

    public function testConfigurationInvokerInvokeOK()
    {
        $isConfigurationHandlerInvokerRaised = [false];
        $configurationFetchedInvoker = new ConfigurationFetchedInvoker(
            Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));

        $now = TimeUtils::currentTimeMillis();
        $status = FetcherStatus::AppliedFromNetwork;
        $hasChanges = true;

        $configurationFetchedInvoker->register(function (ConfigurationFetchedArgs $e)
        use ($now, $status, $configurationFetchedInvoker, &$isConfigurationHandlerInvokerRaised) {

            $this->assertEquals($status, $e->getFetcherStatus());
            $this->assertEquals($now, $e->getCreationDate());
            $this->assertEquals(true, $e->isHasChanges());
            $this->assertEquals(FetcherError::NoError, $e->getErrorDetails());

            $isConfigurationHandlerInvokerRaised[0] = true;
        });

        $configurationFetchedInvoker->invoke($status, $now, $hasChanges);

        $this->assertTrue($isConfigurationHandlerInvokerRaised[0]);
    }

    public function testConfigurationInvokerRaisedUserUnhandledError()
    {
        $invokedCalled = false;
        $userUnhandledErrorInvoker = Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class)
            ->shouldReceive('invoke')
            ->andReturnUsing(function ($sender, $trigger, $ex) use (&$invokedCalled) {
                $this->assertSame(ExceptionTrigger::ConfigurationFetchedHandler, $trigger);
                $invokedCalled = true;
            })
            ->byDefault()
            ->getMock();

        $ex = new Exception("user error");

        $now = time();
        $status = FetcherStatus::AppliedFromNetwork;
        $hasChanges = true;

        $configurationFetchedHandled = function (ConfigurationFetchedArgs $e) use ($status, $now, $ex) {
            $this->assertSame($status, $e->getFetcherStatus());
            $this->assertSame($now, $e->getCreationDate());
            $this->assertTrue($e->isHasChanges());
            $this->assertSame(FetcherError::NoError, $e->getErrorDetails());
            throw $ex;
        };

        $configurationFetchedInvoker = new ConfigurationFetchedInvoker($userUnhandledErrorInvoker);
        $configurationFetchedInvoker->register($configurationFetchedHandled);
        $configurationFetchedInvoker->invoke($status, $now, $hasChanges);
        $this->assertTrue($invokedCalled);
    }
}
