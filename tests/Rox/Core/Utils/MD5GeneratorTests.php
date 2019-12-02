<?php

namespace Rox\Core\Utils;

use Rox\Core\Consts\PropertyType;
use Rox\RoxTestCase;

class MD5GeneratorTests extends RoxTestCase
{
    public function testWillCheckMD5UsesRightProps()
    {
        $props = [];
        $props[PropertyType::getPlatform()->getName()] = "plat";

        $md5 = MD5Generator::generate($props, [PropertyType::getPlatform()]);

        $this->assertEquals($md5, "1380AFEBC7CE22DE7B3450F8CAB86D2C");
    }

    public function testWillCheckMD5NotUsingAllProps()
    {
        $props = [];
        $props[PropertyType::getDevModeSecret()->getName()] = "dev";
        $props[PropertyType::getPlatform()->getName()] = "plat";
        $md5 = MD5Generator::generate($props, [PropertyType::getPlatform()]);
        $this->assertEquals($md5, "1380AFEBC7CE22DE7B3450F8CAB86D2C");
    }

    public function testWillCheckMD5WithObjects()
    {
        $props = [];
        $props[PropertyType::getDevModeSecret()->getName()] = 22;
        $props[PropertyType::getPlatform()->getName()] = true;

        $md5 = MD5Generator::generate($props, [
            PropertyType::getPlatform(),
            PropertyType::getDevModeSecret()]);

        $this->assertEquals($md5, "D3816631EDE04D536EAEB479FE5829FD");
    }

    public function testWillCheckMD5WithJSONObject()
    {
        $props = [];
        $jsonArray = json_decode("[{\"key\": \"value\"}]", true);
        $props[PropertyType::getDevModeSecret()->getName()] = $jsonArray;
        $props[PropertyType::getPlatform()->getName()] = "value";
        $md5 = MD5Generator::generate($props, [
            PropertyType::getPlatform(),
            PropertyType::getDevModeSecret()]);

        $this->assertEquals($md5, "AA16F2AA33D095940A93C991B00D55C7");

        // tests for JSONObject - values are important, no references involved

        // making sure different json array, same content are equals
        $jsonArray2 = [["key" => "value"]];
        $props[PropertyType::getDevModeSecret()->getName()] = $jsonArray2;

        $md5SameArrayDifferentReference = MD5Generator::generate($props, [
            PropertyType::getPlatform(),
            PropertyType::getDevModeSecret()]);

        $this->assertEquals($md5, $md5SameArrayDifferentReference);

        // making sure added items also return different result
        $item2 = ["key2" => "value2"];
        array_push($jsonArray, $item2);
        $props[PropertyType::getDevModeSecret()->getName()] = $jsonArray;

        $md5ChangedArray = MD5Generator::generate($props, [
            PropertyType::getPlatform(),
            PropertyType::getDevModeSecret()]);

        $this->assertNotEquals($md5, $md5ChangedArray);

        // making sure different json gives different result (toString is not just returning "JSONObject")
        $jsonArray = [["key", "value2"]];
        $props[PropertyType::getDevModeSecret()->getName()] = $jsonArray;

        $md5ChangedItem = MD5Generator::generate($props, [
            PropertyType::getPlatform(),
            PropertyType::getDevModeSecret()]);

        $this->assertNotEquals($md5, $md5ChangedItem);
    }
}
