<?php

namespace Rox\Core;

use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\RoxOptionsInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\RoxTestCase;

class CoreTests extends RoxTestCase
{
    public function testWillCheckNullApiKey()
    {
        $mockedSdkSettings = \Mockery::mock(SdkSettingsInterface::class);
        $mockedDeviceProps = \Mockery::mock(DevicePropertiesInterface::class);
        $mockedOptions = \Mockery::mock(RoxOptionsInterface::class);

//            Core c = new Core();
//            await Assert.ThrowsExceptionAsync<System.ArgumentException>(async () => {
//    await c.Setup(mockedSdkSettings.Object, mockedDeviceProps.Object, mockedOptions.Object);
//            });
    }
}