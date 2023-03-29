<?php

namespace Rox\Core\Network;

use Exception;
use Mockery;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\ConfigurationFetchedInvoker;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\RoxTestCase;
use Rox\Core\Consts\Environment;

class ConfigurationFetcherOneSourceTests extends RoxTestCase
{
    private $_dp;
    private $_bu;
    private $_environment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->_dp = Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturn([
                "app_key" => "123",
                "api_version" => "4.0.0",
                "cache_miss_relative_url" => "harta",
                "distinct_id" => "123"
            ])
            ->byDefault()
            ->getMock();

        $this->_bu = Mockery::mock(BUIDInterface::class)
            ->shouldReceive('getQueryStringParts')
            ->andReturn(["buid" => "buid"])
            ->byDefault()
            ->getMock();
            
        $this->_environment = new Environment();
    }

    public function testWillReturnDataWhenSuccessful()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $errorReporter = Mockery::mock(ErrorReporterInterface::class);

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(200, "{\"a\": \"harti\"}"))
            ->getMock();

        $confFetcher = new ConfigurationFetcherOneSource($request, $this->_bu, $this->_dp, $confFetchInvoker, $errorReporter, $this->_environment, ConfigurationSource::Roxy);
        $result = $confFetcher->fetch();

        $this->assertEquals($result->getParsedData()["a"], "harti");
        $this->assertEquals(ConfigurationSource::Roxy, $result->getSource());
        $this->assertEquals(0, $numberOfTimersCalled[0]);
    }

    public function testWillReturnDataWhenSuccessfulAPISource()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $errorReporter = Mockery::mock(ErrorReporterInterface::class);

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(200, "{\"a\": \"harti\"}"))
            ->getMock();

        $confFetcher = new ConfigurationFetcherOneSource($request, $this->_bu, $this->_dp, $confFetchInvoker, $errorReporter, $this->_environment, ConfigurationSource::API);
        $result = $confFetcher->fetch();

        $this->assertEquals($result->getParsedData()["a"], "harti");
        $this->assertEquals(ConfigurationSource::API, $result->getSource());
        $this->assertEquals(0, $numberOfTimersCalled[0]);
    }

    public function testWillReturnNullWhenRoxyFailsWithException()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $errorReporter = Mockery::mock(ErrorReporterInterface::class);

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendPost')
            ->andThrow(Exception::class)
            ->getMock();

        $confFetcher = new ConfigurationFetcherOneSource($request, $this->_bu, $this->_dp, $confFetchInvoker, $errorReporter, $this->_environment);
        $result = $confFetcher->fetch();

        $this->assertEquals($result, null);
        $this->assertEquals(1, $numberOfTimersCalled[0]);
    }

    public function testWillReturnNullWhenRoxyFailsWithHttpStatus()
    {
        $request = Mockery::mock(HttpClientInterface::class);
        $errorReporter = Mockery::mock(ErrorReporterInterface::class);

        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimersCalled = 0;

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->getMock();

        $confFetcher = new ConfigurationFetcherOneSource($request, $this->_bu, $this->_dp, $confFetchInvoker, $errorReporter, $this->_environment);
        $result = $confFetcher->fetch();

        $this->assertEquals($result, null);
        $this->assertEquals(1, $numberOfTimersCalled[0]);
    }
}
