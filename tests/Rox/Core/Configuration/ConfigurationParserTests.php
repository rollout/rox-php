<?php

namespace Rox\Core\Configuration;

use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Core;
use Rox\Core\Network\ConfigurationFetchResult;
use Rox\Core\Network\ConfigurationSource;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\Core\Security\APIKeyVerifierInterface;
use Rox\Core\Security\SignatureVerifierInterface;
use Rox\Core\XPack\Configuration\XConfigurationFetchedInvoker;
use Rox\RoxTestCase;

class ConfigurationParserTests extends RoxTestCase
{
    /**
     * @var SdkSettingsInterface $_sdk
     */
    private $_sdk;

    /**
     * @var SignatureVerifierInterface $_sf
     */
    private $_sf;

    /**
     * @var APIKeyVerifierInterface $_kf
     */
    private $_kf;

    /**
     * @var ErrorReporterInterface $_errRe
     */
    private $_errRe;

    /**
     * @var ConfigurationFetchedInvokerInterface $_cfi
     */
    private $_cfi;

    /**
     * @var ConfigurationFetchedArgs|null $_cfiEvent
     */
    private $_cfiEvent;

    protected function setUp()
    {
        parent::setUp();

        $this->_sdk = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn("12345")
            ->byDefault()
            ->getMock();

        $this->_sf = \Mockery::mock(SignatureVerifierInterface::class)
            ->shouldReceive('verify')
            ->andReturn(true)
            ->byDefault()
            ->getMock();

        $this->_kf = \Mockery::mock(APIKeyVerifierInterface::class)
            ->shouldReceive('verify')
            ->andReturn(true)
            ->byDefault()
            ->getMock();

        $this->_errRe = \Mockery::mock(ErrorReporterInterface::class)
            ->shouldReceive('report')
            ->byDefault()
            ->getMock();

        $this->_cfi = new XConfigurationFetchedInvoker(\Mockery::mock(Core::class));

        $this->_cfi->register(function (ConfigurationFetchedArgs $e) {
            $this->_cfiEvent = $e;
        });
    }

    public function testWillReturnNullWhenUnexpectedException()
    {
        $json = <<<EOT
{
    "nodata":"{\"application\":\"12345\",\"targetGroups\":[{\"condition\":\"eq(true,true)\",\"_id\":\"12345\"},{\"_id\":\"123456\",\"condition\":\"eq(true,true)\"}],\"experiments\":[{\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"FeatureFlags.isFeatureFlagsEnabled\"}],\"archived\":false,\"name\":\"Feature Flags Drawer Item\",\"_id\":\"1\"},{\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"Invitations.isInvitationsEnabled\"}],\"archived\":false,\"name\":\"Enable Modern Invitations\",\"_id\":\"2\"}] } ",
    "signature_v0":"K/bEQCkRXa6+uFr5H2jCRCaVgmtsTwbgfrFGVJ9NebfMH8CgOhCDIvF4TM1Vyyl0bGS9a4r4Qgi/g63NDBWk0ZbRrKAUkVG56V3/bI2GDHxFvRNrNbiPmFv/wmLLuwgh1mdzU0EwLG4M7yXoNXtMr6Jli8t4xfBOaWW1g0QpASkiWa7kdTamVip/1QygyUuhX5hOyUMpy4Ny9Hi/QPvVBn6GDMxQtxpLfTavU9cBly2D7Ex8Z7sUUOKeoEJcdsoF1QzH14XvA2HQSICESz7D/uld0PNdG0tMj9NlAZfki8eY2KuUe/53Z0Og5WrqQUxiAdPuJoZr6+kSqlASZrrkYw==",
    "signed_date":"2018-01-09T19:02:00.720Z"
}           
EOT;

        $configFetchResult = new ConfigurationFetchResult(json_decode($json, true), ConfigurationSource::CDN);

        $cp = new ConfigurationParser($this->_sf, $this->_kf, $this->_errRe, $this->_cfi);
        $conf = $cp->parse($configFetchResult, $this->_sdk);

        $this->assertNull($conf);

        $this->assertNotNull($this->_cfiEvent);
        $this->assertEquals(FetcherError::Unknown, $this->_cfiEvent->getErrorDetails());
    }

    public function testWillReturnNullWhenWrongSignature()
    {
        $this->_cfi->register(function (ConfigurationFetchedArgs $e) use (&$cfiEvent) {
            $cfiEvent = [$e];
        });

        $this->_sf->shouldReceive('verify')
            ->andReturn(false);

        $cp = new ConfigurationParser($this->_sf, $this->_kf, $this->_errRe, $this->_cfi);

        $json = <<<EOT
{
    "data":"{\"application\":\"12345\", \"targetGroups\": [{\"condition\":\"eq(true,true)\",\"_id\":\"12345\"},{\"_id\":\"123456\",\"condition\":\"eq(true,true)\"}], \"experiments\": [{\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"FeatureFlags.isFeatureFlagsEnabled\"}],\"archived\":false,\"name\":\"Feature Flags Drawer Item\",\"_id\":\"1\"}, {\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"Invitations.isInvitationsEnabled\"}],\"archived\":false,\"name\":\"Enable Modern Invitations\",\"_id\":\"2\"}] } ",
    "signature_v0":"wrongK/bEQCkRXa6+uFr5H2jCRCaVgmtsTwbgfrFGVJ9NebfMH8CgOhCDIvF4TM1Vyyl0bGS9a4r4Qgi/g63NDBWk0ZbRrKAUkVG56V3/bI2GDHxFvRNrNbiPmFv/wmLLuwgh1mdzU0EwLG4M7yXoNXtMr6Jli8t4xfBOaWW1g0QpASkiWa7kdTamVip/1QygyUuhX5hOyUMpy4Ny9Hi/QPvVBn6GDMxQtxpLfTavU9cBly2D7Ex8Z7sUUOKeoEJcdsoF1QzH14XvA2HQSICESz7D/uld0PNdG0tMj9NlAZfki8eY2KuUe/53Z0Og5WrqQUxiAdPuJoZr6+kSqlASZrrkYw==",
    "signed_date":"2018-01-09T19:02:00.720Z"
}
EOT;

        $configFetchResult = new ConfigurationFetchResult(json_decode($json, true), ConfigurationSource::API);
        $this->assertNull($cp->parse($configFetchResult, null));
        $this->assertNotNull($this->_cfiEvent);
        $this->assertEquals(FetcherError::SignatureVerificationError, $this->_cfiEvent->getErrorDetails());
    }

    public function testWillReturnNullWhenWrongApiKey()
    {
        $json = <<<EOT
{
    "data":"{\"application\":\"12345\", \"targetGroups\": [{\"condition\":\"eq(true,true)\",\"_id\":\"12345\"},{\"_id\":\"123456\",\"condition\":\"eq(true,true)\"}], \"experiments\": [{\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"FeatureFlags.isFeatureFlagsEnabled\"}],\"archived\":false,\"name\":\"Feature Flags Drawer Item\",\"_id\":\"1\"}, {\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"Invitations.isInvitationsEnabled\"}],\"archived\":false,\"name\":\"Enable Modern Invitations\",\"_id\":\"2\"}] } ",
    "signature_v0":"K/bEQCkRXa6+uFr5H2jCRCaVgmtsTwbgfrFGVJ9NebfMH8CgOhCDIvF4TM1Vyyl0bGS9a4r4Qgi/g63NDBWk0ZbRrKAUkVG56V3/bI2GDHxFvRNrNbiPmFv/wmLLuwgh1mdzU0EwLG4M7yXoNXtMr6Jli8t4xfBOaWW1g0QpASkiWa7kdTamVip/1QygyUuhX5hOyUMpy4Ny9Hi/QPvVBn6GDMxQtxpLfTavU9cBly2D7Ex8Z7sUUOKeoEJcdsoF1QzH14XvA2HQSICESz7D/uld0PNdG0tMj9NlAZfki8eY2KuUe/53Z0Og5WrqQUxiAdPuJoZr6+kSqlASZrrkYw==",
    "signed_date":"2018-01-09T19:02:00.720Z"
}       
EOT;

        $configFetchResult = new ConfigurationFetchResult(json_decode($json, true), ConfigurationSource::API);

        $this->_kf->shouldReceive('verify')
            ->andReturn(false)
            ->getMock();

        $cp = new ConfigurationParser($this->_sf, $this->_kf, $this->_errRe, $this->_cfi);
        $this->assertNull($cp->parse($configFetchResult, $this->_sdk));
        $this->assertNotNull($this->_cfiEvent);
        $this->assertEquals(FetcherError::MismatchAppKey, $this->_cfiEvent->getErrorDetails());
    }

    public function testWillParseExperimentsAndTargetGroups()
    {
        $json = <<<EOT
{
    "data":"{\"application\":\"12345\", \"targetGroups\": [{\"condition\":\"eq(true, true)\",\"_id\":\"12345\"},{\"_id\":\"123456\",\"condition\":\"eq(true, true)\"}], \"experiments\": [{\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"FeatureFlags.isFeatureFlagsEnabled\"}],\"archived\":false,\"name\":\"Feature Flags Drawer Item\",\"_id\":\"1\",\"labels\":[\"label1\"]}, {\"deploymentConfiguration\":{\"condition\":\"ifThen(and(true, true)\"},\"featureFlags\":[{\"name\":\"Invitations.isInvitationsEnabled\"}],\"archived\":false,\"name\":\"Enable Modern Invitations\",\"_id\":\"2\"}] } ",
    "signature_v0":"K/bEQCkRXa6+uFr5H2jCRCaVgmtsTwbgfrFGVJ9NebfMH8CgOhCDIvF4TM1Vyyl0bGS9a4r4Qgi/g63NDBWk0ZbRrKAUkVG56V3/bI2GDHxFvRNrNbiPmFv/wmLLuwgh1mdzU0EwLG4M7yXoNXtMr6Jli8t4xfBOaWW1g0QpASkiWa7kdTamVip/1QygyUuhX5hOyUMpy4Ny9Hi/QPvVBn6GDMxQtxpLfTavU9cBly2D7Ex8Z7sUUOKeoEJcdsoF1QzH14XvA2HQSICESz7D/uld0PNdG0tMj9NlAZfki8eY2KuUe/53Z0Og5WrqQUxiAdPuJoZr6+kSqlASZrrkYw==",
    "signed_date":"2018-01-09T19:02:00.720Z"
}
EOT;

        $configFetchResult = new ConfigurationFetchResult(json_decode($json, true), ConfigurationSource::API);

        $cp = new ConfigurationParser($this->_sf, $this->_kf, $this->_errRe, $this->_cfi);
        $conf = $cp->parse($configFetchResult, $this->_sdk);

        $this->assertNotNull($conf);

        $this->assertEquals(count($conf->getTargetGroups()), 2);
        $this->assertEquals($conf->getTargetGroups()[0]->getId(), "12345");
        $this->assertEquals($conf->getTargetGroups()[0]->getCondition(), "eq(true, true)");
        $this->assertEquals($conf->getTargetGroups()[1]->getId(), "123456");
        $this->assertEquals($conf->getTargetGroups()[1]->getCondition(), "eq(true, true)");

        $this->assertEquals(count($conf->getExperiments()), 2);
        $this->assertEquals($conf->getExperiments()[0]->getCondition(), "ifThen(and(true, true)");
        $this->assertEquals($conf->getExperiments()[0]->getName(), "Feature Flags Drawer Item");
        $this->assertEquals($conf->getExperiments()[0]->getId(), "1");
        $this->assertEquals($conf->getExperiments()[0]->isArchived(), false);
        $this->assertEquals(count($conf->getExperiments()[0]->getFlags()), 1);
        $this->assertEquals($conf->getExperiments()[0]->getFlags()[0], "FeatureFlags.isFeatureFlagsEnabled");
        $this->assertEquals(count($conf->getExperiments()[0]->getLabels()), 1);
        $this->assertEquals($conf->getExperiments()[0]->getLabels()[0], "label1");
        $this->assertEquals($conf->getExperiments()[1]->getCondition(), "ifThen(and(true, true)");
        $this->assertEquals($conf->getExperiments()[1]->getName(), "Enable Modern Invitations");
        $this->assertEquals($conf->getExperiments()[1]->getId(), "2");
        $this->assertEquals($conf->getExperiments()[1]->isArchived(), false);
        $this->assertEquals(count($conf->getExperiments()[1]->getFlags()), 1);
        $this->assertEquals($conf->getExperiments()[1]->getFlags()[0], "Invitations.isInvitationsEnabled");
        $this->assertEquals(count($conf->getExperiments()[1]->getLabels()), 0);

        $this->assertNull($this->_cfiEvent);
    }
}
