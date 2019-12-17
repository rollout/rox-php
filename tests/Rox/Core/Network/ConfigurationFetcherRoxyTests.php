<?php

namespace Rox\Core\Network;

use Exception;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\ConfigurationFetchedInvoker;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\RoxTestCase;

class ConfigurationFetcherRoxyTests extends RoxTestCase
{
    private $_dp;
    private $_bu;

    protected function setUp()
    {
        parent::setUp();

        $this->_dp = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturn([
                "app_key" => "123",
                "api_version" => "4.0.0",
                "cache_miss_relative_url" => "harta",
                "distinct_id" => "123"
            ])
            ->byDefault()
            ->getMock();

        $this->_bu = \Mockery::mock(BUIDInterface::class);
    }

    public function testWillReturnDataWhenSuccessful()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker();
        $errorReporter = \Mockery::mock(ErrorReporterInterface::class);

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(200, "{\"a\": \"harti\"}"))
            ->getMock();

        $confFetcher = new ConfigurationFetcherRoxy($request, $this->_dp, $this->_bu, $confFetchInvoker, "http://harta.com", $errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($result->getParsedData()["a"], "harti");
        $this->assertEquals(ConfigurationSource::Roxy, $result->getSource());
        $this->assertEquals(0, $numberOfTimersCalled[0]);
    }

    public function testWillReturnNullWhenRoxyFailsWithException()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker();
        $errorReporter = \Mockery::mock(ErrorReporterInterface::class);

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andThrow(Exception::class)
            ->getMock();

        $confFetcher = new ConfigurationFetcherRoxy($request, $this->_dp, $this->_bu, $confFetchInvoker, "http://harta.com", $errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($result, null);
        $this->assertEquals(1, $numberOfTimersCalled[0]);
    }

    public function testWillReturnNullWhenRoxyFailsWithHttpStatus()
    {
        $request = \Mockery::mock(HttpClientInterface::class);
        $errorReporter = \Mockery::mock(ErrorReporterInterface::class);

        $confFetchInvoker = new ConfigurationFetchedInvoker();
        $numberOfTimersCalled = 0;

        $numberOfTimersCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimersCalled) {
            $numberOfTimersCalled[0]++;
        });

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->getMock();

        $confFetcher = new ConfigurationFetcherRoxy($request, $this->_dp, $this->_bu, $confFetchInvoker, "http://harta.com", $errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($result, null);
        $this->assertEquals(1, $numberOfTimersCalled[0]);
    }
}
