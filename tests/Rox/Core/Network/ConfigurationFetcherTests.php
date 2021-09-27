<?php

namespace Rox\Core\Network;

use Exception;
use Mockery;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\ConfigurationFetchedInvoker;
use Rox\Core\Consts\PropertyType;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\RoxTestCase;

class ConfigurationFetcherTests extends RoxTestCase
{
    /**
     * @var DevicePropertiesInterface
     */
    private $_dp;

    /**
     * @var BUIDInterface
     */
    private $_bu;

    /**
     * @var ErrorReporterInterface
     */
    private $_errorReporter;

    protected function setUp()
    {
        parent::setUp();

        $this->_dp = Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturn([
                "app_key" => "123",
                "api_version" => "4.0.0",
                "distinct_id" => "id"
            ])
            ->byDefault()
            ->getMock();

        $this->_bu = Mockery::mock(BUIDInterface::class)
            ->shouldReceive('getQueryStringParts')
            ->andReturn(["buid" => "buid"])
            ->byDefault()
            ->getMock();

        $this->_errorReporter = Mockery::mock(ErrorReporterInterface::class)
            ->shouldReceive('report')
            ->byDefault()
            ->getMock();
    }

    public function testWillReturnCDNDataWhenSuccessful()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));

        $numberOfTimerCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $response = new TestHttpResponse(200, "{\"a\": \"harti\"}");

        $reqData = [null];
        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use ($response, &$reqData) {
                $reqData[0] = $req;
                return $response;
            })
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($reqData[0]->getUrl(), "https://conf.rollout.io/123/buid");
        $this->assertEquals(count($reqData[0]->getQueryParams()), 1);
        $this->assertEquals($reqData[0]->getQueryParams()[PropertyType::getDistinctId()->getName()], "id");

        $this->assertEquals("harti", $result->getParsedData()["a"]);
        $this->assertEquals(ConfigurationSource::CDN, $result->getSource());

        $this->assertEquals(0, $numberOfTimerCalled[0]);
    }

    public function testWillReturnNullWhenCDNFailsWithException()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andThrow(new Exception('not found'))
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(200, "{\"a: harto\"}"))
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertNull($result);
        $this->assertEquals(1, $numberOfTimerCalled[0]);
    }

    public function testWillReturnNullWhenCDNSucceedWithEmptyResponse()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(200, ""))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->never()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($result, null);
        $this->assertEquals(1, $numberOfTimerCalled[0]);

        $logger = $this->_loggerFactory->getLogger();
        $this->assertEquals(1, count($logger->records));
        $this->assertTrue($logger->hasDebug("Failed to parse JSON configuration - Null Or Empty"));
    }

    public function testWillReturnNullWhenCDNSucceedWithNotJsonResponse()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];
        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->once()
            ->andReturn(new TestHttpResponse(200, "{fdsadf/:"))
            ->getMock()
            ->shouldReceive('sendPost')
            ->never()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertNull($result);
        $this->assertEquals(1, $numberOfTimerCalled[0]);

        $logger = $this->_loggerFactory->getLogger();
        $this->assertEquals(1, count($logger->records));
        $this->assertTrue($logger->hasDebug("Failed to parse JSON configuration"));
    }

    public function testWillReturnNullWhenCDNFails404APIWithException()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andThrow(new Exception())
            ->once()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertNull($result);
        $this->assertEquals(1, $numberOfTimerCalled[0]);
    }

    public function testWillReturnAPIDataWhenCDNFailsWithResult404APIOK()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $reqData = [null];
        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_OK, "{\"result\": \"404\"}"))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(HttpResponseInterface::STATUS_OK, "{\"harto\": \"a\"}");
            })
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals($reqData[0]->getUrl(), "https://x-api.rollout.io/device/get_configuration/123/buid");

        $allDeviceProps = $this->_dp->getAllProperties();
        $queryStringParts = $this->_bu->getQueryStringParts();
        foreach (array_keys($queryStringParts) as $key) {
            $allDeviceProps[$key] = $queryStringParts[$key];
        }

        $allDeviceProps[PropertyType::getCacheMissRelativeUrl()->getName()] = "123/buid";

        $queryParams = $reqData[0]->getQueryParams();
        $this->assertEquals(count($allDeviceProps), count($queryParams));

        foreach (array_keys($queryParams) as $key) {
            $this->assertEquals($queryParams[$key], $allDeviceProps[$key]);
        }

        $this->assertEquals("a", $result->getParsedData()["harto"]);
        $this->assertEquals(ConfigurationSource::API, $result->getSource());
        $this->assertEquals(0, $numberOfTimerCalled[0]);

    }

    public function testWillReturnAPIDataWhenCDNSucceedWithResult200()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(200, "{\"result\": \"200\"}"))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(200, "{\"harto\": \"a\"}"))
            ->never()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals("200", $result->getParsedData()["result"]);
        $this->assertEquals(ConfigurationSource::CDN, $result->getSource());
        $this->assertEquals(0, $numberOfTimerCalled[0]);
    }

    public function testWillReturnAPIDataWhenCDNFails404APIOK()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(200, "{\"a\": \"harto\"}"))
            ->once()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertEquals("harto", $result->getParsedData()["a"]);
        $this->assertEquals(ConfigurationSource::API, $result->getSource());
        $this->assertEquals(0, $numberOfTimerCalled[0]);
    }

    public function testWillReturnNullDataWhenBothNotFound()
    {
        $confFetchInvoker = new ConfigurationFetchedInvoker(Mockery::mock(UserspaceUnhandledErrorInvokerInterface::class));
        $numberOfTimerCalled = [0];

        $confFetchInvoker->register(function (ConfigurationFetchedArgs $e) use (&$numberOfTimerCalled) {
            $numberOfTimerCalled[0]++;
        });

        $request = Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturn(new TestHttpResponse(HttpResponseInterface::STATUS_NOT_FOUND))
            ->once()
            ->getMock();

        $confFetcher = new ConfigurationFetcher($request, $this->_bu, $this->_dp, $confFetchInvoker, $this->_errorReporter);
        $result = $confFetcher->fetch();

        $this->assertNull($result);
        $this->assertEquals(1, $numberOfTimerCalled[0]);
    }
}
