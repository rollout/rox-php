<?php

namespace Rox\Core;

use InvalidArgumentException;
use Rox\Core\Client\DeviceProperties;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\RoxOptionsInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\RoxTestCase;

class CoreTests extends RoxTestCase
{
    public function testWillCheckNullApiKey()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn(null)
            ->getMock();
        $mockedDeviceProps = \Mockery::mock(DevicePropertiesInterface::class);
        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class)
            ->shouldReceive('getRoxyURL')
            ->andReturnNull()
            ->getMock();

        $c = new Core();
        $c->setup($mockedSdkSettings, $mockedDeviceProps, $mockedOptions);
    }

    public function testWillCheckEmptyApiKey()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn('')
            ->getMock();

        $mockedDeviceProps = \Mockery::mock(DevicePropertiesInterface::class);
        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class);

        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class)
            ->shouldReceive('getRoxyURL')
            ->andReturnNull()
            ->getMock();

        $c = new Core();
        $c->setup($mockedSdkSettings, $mockedDeviceProps, $mockedOptions);
    }

    public function testWillCheckInvalidApiKey()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn('aaaaaaaaaaaaaaaaaaaaaaag')
            ->getMock();

        $mockedDeviceProps = \Mockery::mock(DevicePropertiesInterface::class);

        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class)
            ->shouldReceive('getRoxyURL')
            ->andReturnNull()
            ->getMock();

        $c = new Core();
        $c->setup($mockedSdkSettings, $mockedDeviceProps, $mockedOptions);
    }

    public function testWillCheckCoreSetupWhenOptionsWithRoxy()
    {
        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn('doesn\'t matter')
            ->getMock();
        $mockedDeviceProps = \Mockery::mock(DevicePropertiesInterface::class);
        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class)
            ->shouldReceive('getRoxyURL')
            ->andReturn('http://site.com')
            ->getMock()
            ->shouldReceive('getConfigurationFetchedHandler')
            ->andReturnNull()
            ->getMock()
            ->shouldReceive('getImpressionHandler')
            ->andReturnNull()
            ->getMock()
            ->shouldReceive('getDynamicPropertiesRule')
            ->andReturnNull()
            ->getMock();

        $c = new Core();
        $c->setup($mockedSdkSettings, $mockedDeviceProps, $mockedOptions);
    }

    public function testWillCheckCoreSetupWhenNoOptions()
    {
        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn('aaaaaaaaaaaaaaaaaaaaaaaa')
            ->getMock();

        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class)
            ->shouldReceive('getVersion')
            ->andReturnNull()
            ->getMock()
            ->shouldReceive('getDevModeKey')
            ->andReturnNull()
            ->getMock();

        $dp = new DeviceProperties($mockedSdkSettings, $mockedOptions);

        $c = new Core();
        $c->setup($mockedSdkSettings, $dp, null);
    }
}
