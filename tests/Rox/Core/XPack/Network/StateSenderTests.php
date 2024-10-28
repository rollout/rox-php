<?php

namespace Rox\Core\XPack\Network;

use Exception;
use Psr\Log\Test\TestLogger;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Consts\PropertyType;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyRepository;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\HttpClientInterface;
use Rox\Core\Network\RequestData;
use Rox\Core\Network\TestHttpResponse;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Utils\DotNetCompat;
use Rox\RoxTestCase;
use Rox\Server\Flags\RoxFlag;
use Rox\Core\Consts\Environment;
use Rox\Server\RoxOptions;
use Rox\Server\NetworkConfigurationsOptions;
use Rox\Server\RoxOptionsBuilder;
use Ramsey\Uuid\Uuid;
use DateTime;

class StateSenderTests extends RoxTestCase
{
    /**
     * @var DevicePropertiesInterface $_dp
     */
    private $_dp;

    /**
     * @var FlagRepositoryInterface $_flagRepo
     */
    private $_flagRepo;

    /**
     * @var CustomPropertyRepositoryInterface $_cpRepo
     */
    private $_cpRepo;

    /**
     * @var string $_appKey
     */
    private $_appKey = "123";

    /**
     * @var TestLogger $_log
     */
    private $_log;

    /**
     * @var Environment $_environment
     */
    private $_environment;

    /**
     * @return array
     */
    private function _createNewDeviceProp()
    {
        return [
            "platform" => ".net",
            "devModeSecret" => "shh...",
            "app_key" => $this->_appKey,
            "api_version" => "4.0.0"
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->_environment = new Environment();
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        ;

        $this->_dp = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturnUsing(function () {
                return $this->_createNewDeviceProp();
            })
            ->byDefault()
            ->getMock();

        $this->_dp->shouldReceive('getRolloutKey')
            ->andReturnUsing(function () {
                return $this->_appKey;
            })
            ->byDefault()
            ->getMock();

        $this->_cpRepo = new CustomPropertyRepository();
        $this->_flagRepo = new FlagRepository();
    }

    private function _validateNoErrors()
    {
        $this->assertFalse($this->_log->hasErrorRecords());
    }

    public function testWillCallCDNSuccessfully()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag1");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals("/{$this->_appKey}/09FBB5C9B28B300E8FF14BE4EBB5A829", parse_url($reqData[0]->getUrl())['path']);

        $this->_validateNoErrors();
    }

    public function testWillNotCallCDNTwice()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag1");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/09FBB5C9B28B300E8FF14BE4EBB5A829");
        $this->_validateNoErrors();

        $reqData[0] = null;
        $stateSender->send();
        $this->assertNull($reqData[0]);
        $this->_validateNoErrors();
    }

    public function testWillCallOnlyCDNStateMD5ChangesForFlag()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->twice()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag1");
        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/09FBB5C9B28B300E8FF14BE4EBB5A829");

        $this->_flagRepo->addFlag(new RoxFlag(), "flag2");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/70748DE9C7F33257E8D2E6B6D7F13C21");
    }

    public function testWillCallOnlyCDNStateMD5ChangesForCustomProperty()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->twice()
            ->getMock();

        $this->_cpRepo->addCustomProperty(new CustomProperty("cp1", CustomPropertyType::getString(), "true"));
        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/81E3F0A97C49E64CA5E47558F2DFC028");

        $this->_cpRepo->addCustomProperty(new CustomProperty("cp2", CustomPropertyType::getDouble(), 20));
        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/A5D9A87DB72A5A8DA0E571408E81A0A9");
    }

    public function testWillCallOnlyCDNStateMD5FlagOrderNotImportant()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->times(3)
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag1");
        $this->_flagRepo->addFlag(new RoxFlag(), "flag2");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/70748DE9C7F33257E8D2E6B6D7F13C21");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $fr2 = new FlagRepository();
        $stateSender2 = new StateSender($request, $this->_dp, $fr2, $this->_cpRepo, $this->_environment);
        $fr2->addFlag(new RoxFlag(), "flag2");
        $fr2->addFlag(new RoxFlag(), "flag1");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender2->send();
        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/70748DE9C7F33257E8D2E6B6D7F13C21");
    }

    public function testWillCallOnlyCDNStateMD5CustomPropertyOrderNotImportant()
    {
        $reqData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqData) {
                $reqData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->times(3)
            ->getMock();

        $this->_cpRepo->addCustomProperty(new CustomProperty("cp1", CustomPropertyType::getString(), "1111"));
        $this->_cpRepo->addCustomProperty(new CustomProperty("cp2", CustomPropertyType::getString(), "2222"));
        ;

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/F8B8EC489E8F944187BA8343537B14BA");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();
        $cpr2 = new CustomPropertyRepository();
        $stateSender2 = new StateSender($request, $this->_dp, $this->_flagRepo, $cpr2, $this->_environment);
        $cpr2->addCustomProperty(new CustomProperty("cp2", CustomPropertyType::getString(), "2222"));
        ;
        $cpr2->addCustomProperty(new CustomProperty("cp1", CustomPropertyType::getString(), "1111"));

        $stateSender2->send();
        $this->_validateNoErrors();

        $this->assertEquals(parse_url($reqData[0]->getUrl())['path'], "/{$this->_appKey}/F8B8EC489E8F944187BA8343537B14BA");
    }

    public function testWillReturnNullWhenCDNFailsWithException()
    {
        $reqCDNData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                throw new Exception("not found");
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->never()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasErrorThatContains("Failed to send state"));

        $this->assertEquals(parse_url($reqCDNData[0]->getUrl())['path'], "/{$this->_appKey}/4C40B3DADA2F857113A19056C3A06270");
    }

    public function testWillReturnNullWhenCDNSucceedWithEmptyResponse()
    {
        $reqCDNData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(200, '');
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->never()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasErrorThatContains("Failed to send state"));
    }

    public function testWillReturnNullWhenCDNSucceedWithNotJsonResponse()
    {
        $reqCDNData = [null];
        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(200, 'fdsafdas{');
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->never()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasErrorThatContains("Failed to send state"));
    }

    public function testWillReturnNullWhenCDNFails404APIWithException()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(404);
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData) {
                $reqAPIData[0] = $req;
                throw new Exception("not found");
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");
        $this->_validateRequestParams($reqAPIData[0]);

        $this->assertEquals(2, count($this->_log->records));
        $this->assertTrue($this->_log->hasDebugThatContains("Failed to send state"));
        $this->assertTrue($this->_log->hasErrorThatContains("Failed to send state"));
    }

    public function testWillReturnCallOnlyApiSuccessfully()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];
        $reqCDNRequestNumber = 0;
        $reqAPIRequestNumber = 0;

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData, &$reqCDNRequestNumber) {
                $reqCDNData[$reqCDNRequestNumber] = $req;
                $reqCDNRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->never()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData, &$reqAPIRequestNumber) {
                $reqAPIData[$reqAPIRequestNumber] = $req;
                $reqAPIRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));
        $optionBuilder = new RoxOptionsBuilder();
        $optionBuilder->setNetworkConfigurationsOptions(
            new NetworkConfigurationsOptions(null, null, 'https://x-api.rollout.io/device/update_state_store', null, null)
        );
        $envNoCdn = new Environment(new RoxOptions($optionBuilder));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $envNoCdn);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");
        $this->_validateNoErrors();
        $this->assertEquals(0, $reqCDNRequestNumber);
        $this->assertEquals(1, $reqAPIRequestNumber);
    }

    public function testWillReturnCallOnlyCBPApiSuccessfullyIfUUIDKeyGiven()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];
        $reqCDNRequestNumber = 0;
        $reqAPIRequestNumber = 0;

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData, &$reqCDNRequestNumber) {
                $reqCDNData[$reqCDNRequestNumber] = $req;
                $reqCDNRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->never()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData, &$reqAPIRequestNumber) {
                $reqAPIData[$reqAPIRequestNumber] = $req;
                $reqAPIRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->once()
            ->getMock();

        $this->_dp->shouldReceive('getRolloutKey')
            ->andReturnUsing(function () {
                return Uuid::uuid4();
            })
            ->byDefault()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getDateTime(), new DateTime("now")));
        $optionBuilder = new RoxOptionsBuilder();
        $optionBuilder->setNetworkConfigurationsOptions(
            new NetworkConfigurationsOptions(null, null, 'https://api.cloudbees.io/device/update_state_store', null, null)
        );
        $envNoCdn = new Environment(new RoxOptions($optionBuilder));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $envNoCdn);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/EB41037B6A5CC68AA005E5C7A28E9FA6");
        $this->_validateNoErrors();
        $this->assertEquals(0, $reqCDNRequestNumber);
        $this->assertEquals(1, $reqAPIRequestNumber);
        // This asserts that there is externalType field, which is only for CBP
        $this->assertEquals("DateTime", $reqAPIData[0]->getQueryParams()['custom_properties'][0]['externalType']);
        $this->assertEquals("Boolean", $reqAPIData[0]->getQueryParams()['feature_flags'][0]['externalType']);
    }

    public function testSkipAddingDateTimePropertyIfNotCBPKeyGiven()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];
        $reqCDNRequestNumber = 0;
        $reqAPIRequestNumber = 0;

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData, &$reqCDNRequestNumber) {
                $reqCDNData[$reqCDNRequestNumber] = $req;
                $reqCDNRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->never()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData, &$reqAPIRequestNumber) {
                $reqAPIData[$reqAPIRequestNumber] = $req;
                $reqAPIRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->once()
            ->getMock();

        $this->_dp->shouldReceive('getRolloutKey')
            ->andReturnUsing(function () {
                return '64c13ab08edf48a008793cac';
            })
            ->byDefault()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id.string", CustomPropertyType::getString(), "1111"));
        $this->_cpRepo->addCustomProperty(new CustomProperty("id.now", CustomPropertyType::getDateTime(), new DateTime("now")));
        $optionBuilder = new RoxOptionsBuilder();
        $optionBuilder->setNetworkConfigurationsOptions(
            new NetworkConfigurationsOptions(null, null, 'https://api.cloudbees.io/device/update_state_store', null, null)
        );
        $envNoCdn = new Environment(new RoxOptions($optionBuilder));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $envNoCdn);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/CE6ABC2C4A3017819E16086D5D1D9BD1");
        $this->_validateNoErrors();
        $this->assertEquals(0, $reqCDNRequestNumber);
        $this->assertEquals(1, $reqAPIRequestNumber);

        // This asserts that custom properties did not add "DateTime" property, and does not have externalType key for feature flag
        $this->assertEquals(1, count($reqAPIData[0]->getQueryParams()['custom_properties']));
        $this->assertEquals("String", $reqAPIData[0]->getQueryParams()['custom_properties'][0]['externalType']);
        $this->assertEquals("string", $reqAPIData[0]->getQueryParams()['custom_properties'][0]['type']);
        $this->assertArrayNotHasKey("externalType", $reqAPIData[0]->getQueryParams()['feature_flags'][0]);
    }

    public function testWillReturnNullCallOnlyApiFailed404()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];
        $reqCDNRequestNumber = 0;
        $reqAPIRequestNumber = 0;

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData, &$reqCDNRequestNumber) {
                $reqCDNData[$reqCDNRequestNumber] = $req;
                $reqCDNRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->never()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData, &$reqAPIRequestNumber) {
                $reqAPIData[$reqAPIRequestNumber] = $req;
                $reqAPIRequestNumber++;
                return new TestHttpResponse(404);
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));
        $optionBuilder = new RoxOptionsBuilder();
        $optionBuilder->setNetworkConfigurationsOptions(
            new NetworkConfigurationsOptions(null, null, 'https://x-api.rollout.io/device/update_state_store', null, null)
        );
        $envNoCdn = new Environment(new RoxOptions($optionBuilder));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $envNoCdn);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasDebugThatContains("Failed to send state to API"));

        $this->assertEquals(0, $reqCDNRequestNumber);
        $this->assertEquals(1, $reqAPIRequestNumber);
    }

    public function testWillReturnNullCallOnlyApiFailedException()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];
        $reqCDNRequestNumber = 0;
        $reqAPIRequestNumber = 0;

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData, &$reqCDNRequestNumber) {
                $reqCDNData[$reqCDNRequestNumber] = $req;
                $reqCDNRequestNumber++;
                return new TestHttpResponse(200, "{\"result\": \"200\"}");
            })
            ->never()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData, &$reqAPIRequestNumber) {
                $reqAPIData[$reqAPIRequestNumber] = $req;
                $reqAPIRequestNumber++;
                throw new Exception("not found");
                ;
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));
        $optionBuilder = new RoxOptionsBuilder();
        $optionBuilder->setNetworkConfigurationsOptions(
            new NetworkConfigurationsOptions(null, null, 'https://x-api.rollout.io/device/update_state_store', null, null)
        );
        $envNoCdn = new Environment(new RoxOptions($optionBuilder));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $envNoCdn);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasErrorThatContains("Failed to send state. Source: API"));

        $this->assertEquals(0, $reqCDNRequestNumber);
        $this->assertEquals(1, $reqAPIRequestNumber);
    }

    public function testWillReturnAPIDataWhenCDNSucceedWithResult404APIOK()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(200, "{\"result\": \"404\"}");
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData) {
                $reqAPIData[0] = $req;
                return new TestHttpResponse(200);
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));
        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");
        $this->_validateRequestParams($reqAPIData[0]);

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasDebugThatContains("Failed to send state to "));
    }

    public function testWillReturnAPIDataWhenCDNFails404APIOK()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(404);
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData) {
                $reqAPIData[0] = $req;
                return new TestHttpResponse(200);
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");
        $this->_cpRepo->addCustomProperty(new CustomProperty("id", CustomPropertyType::getString(), "1111"));

        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(parse_url($reqAPIData[0]->getUrl())['path'], "/device/update_state_store/{$this->_appKey}/3A8C38DAE0553488B96AA0EB5508C4CC");
        $this->_validateRequestParams($reqAPIData[0]);

        $this->assertEquals(1, count($this->_log->records));
        $this->assertTrue($this->_log->hasDebugThatContains("Failed to send state to "));
    }

    private function _validateRequestParams(RequestData $reqAPIData)
    {
        $this->assertEquals($reqAPIData->getQueryParams()[PropertyType::getPlatform()->getName()], ".net");
        $this->assertEquals(DotNetCompat::toJson($reqAPIData->getQueryParams()[PropertyType::getFeatureFlags()->getName()]), "[\n  {\n    \"name\": \"flag\",\n    \"defaultValue\": \"false\",\n    \"options\": [\n      \"false\",\n      \"true\"\n    ]\n  }\n]");
        $this->assertEquals(DotNetCompat::toJson($reqAPIData->getQueryParams()[PropertyType::getCustomProperties()->getName()]), "[\n  {\n    \"name\": \"id\",\n    \"type\": \"string\",\n    \"externalType\": \"String\"\n  }\n]");
        $this->assertEquals(DotNetCompat::toJson($reqAPIData->getQueryParams()[PropertyType::getRemoteVariables()->getName()]), "[]");
        $this->assertEquals($reqAPIData->getQueryParams()[PropertyType::getDevModeSecret()->getName()], "shh...");
    }

    public function testWillReturnNullDataWhenBothNotFound()
    {
        $reqCDNData = [null];
        $reqAPIData = [null];

        $request = \Mockery::mock(HttpClientInterface::class)
            ->shouldReceive('sendGet')
            ->andReturnUsing(function ($req) use (&$reqCDNData) {
                $reqCDNData[0] = $req;
                return new TestHttpResponse(404);
            })
            ->once()
            ->getMock()
            ->shouldReceive('sendPost')
            ->andReturnUsing(function ($req) use (&$reqAPIData) {
                $reqAPIData[0] = $req;
                return new TestHttpResponse(404);
            })
            ->once()
            ->getMock();

        $this->_flagRepo->addFlag(new RoxFlag(), "flag");


        $stateSender = new StateSender($request, $this->_dp, $this->_flagRepo, $this->_cpRepo, $this->_environment);
        $stateSender->send();

        $this->assertEquals(2, count($this->_log->records));
        $this->assertTrue($this->_log->hasDebugThatContains("Failed to send state"));
    }
}
