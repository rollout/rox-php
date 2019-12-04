<?php

namespace Rox\Core\XPack\Client;

use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\RoxTestCase;

class XBUIDTests extends RoxTestCase
{
    public function testWillGenerateCorrectMD5Value()
    {
        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturn([
                "app_key" => "123",
                "api_version" => "4.0.0",
                "platform" => "plat",
                "lib_version" => "1.5.0"
            ])
            ->byDefault()
            ->getMock();

        $sdkSettings = \Mockery::mock(SdkSettingsInterface::class);


        $buid = new XBUID($sdkSettings, $deviceProps);
        $this->assertEquals($buid->getValue(), "234A32BB4341EAFD91FC8D0395F4E66F");

        $deviceProps2 = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getAllProperties')
            ->andReturn([
                "app_key" => "122",
                "api_version" => "4.0.0",
                "platform" => "plat",
                "lib_version" => "1.5.0"
            ])
            ->byDefault()
            ->getMock();

        $buid2 = new XBUID($sdkSettings, $deviceProps2);
        $this->assertEquals($buid2->getValue(), "F5F30C84B8A806E0004043864724A56E");
    }
}
