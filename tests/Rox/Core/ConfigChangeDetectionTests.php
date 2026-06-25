<?php

namespace Rox\Core;

use Doctrine\Common\Cache\FilesystemCache;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Network\ConfigurationFetchResult;
use Rox\Core\Network\ConfigurationSource;
use Rox\RoxTestCase;

const TEST_API_KEY = 'aaaaaaaaaaaaaaaaaaaaaaaa';
const TEST_CACHE_KEY = 'config_hash_' . TEST_API_KEY;

class ConfigChangeDetectionTests extends RoxTestCase
{
    /**
     * @var FilesystemCache
     */
    private $_cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_cache = new FilesystemCache(join(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            'rollout',
            'cache'
        ]));
        $this->_cache->delete(TEST_CACHE_KEY);
    }

    protected function tearDown(): void
    {
        $this->_cache->delete(TEST_CACHE_KEY);
        parent::tearDown();
    }

    private function _callDetect(Core $core, ConfigurationFetchResult $result)
    {
        $method = new \ReflectionMethod(Core::class, '_detectAndPersistConfigChanges');
        $method->setAccessible(true);
        return $method->invoke($core, $result);
    }

    private function _makeCore()
    {
        $sdkSettings = \Mockery::mock(SdkSettingsInterface::class)
            ->shouldReceive('getApiKey')
            ->andReturn('aaaaaaaaaaaaaaaaaaaaaaaa')
            ->getMock();

        $core = new Core();

        $prop = new \ReflectionProperty(Core::class, '_sdkSettings');
        $prop->setAccessible(true);
        $prop->setValue($core, $sdkSettings);

        return $core;
    }

    public function testHasChangesIsTrueOnFirstFetch()
    {
        $core = $this->_makeCore();
        $result = new ConfigurationFetchResult(['flag' => 'on'], ConfigurationSource::CDN);

        $this->assertTrue($this->_callDetect($core, $result));
    }

    public function testHasChangesIsFalseWhenConfigUnchangedAcrossProcesses()
    {
        $core = $this->_makeCore();
        $result = new ConfigurationFetchResult(['flag' => 'on'], ConfigurationSource::CDN);

        // First call simulates the previous process writing the hash
        $this->_callDetect($core, $result);

        // Second call simulates a new process with the same config
        $core2 = $this->_makeCore();
        $this->assertFalse($this->_callDetect($core2, $result));
    }

    public function testHasChangesIsTrueWhenConfigChangedAcrossProcesses()
    {
        $core = $this->_makeCore();
        $oldResult = new ConfigurationFetchResult(['flag' => 'on'], ConfigurationSource::CDN);
        $this->_callDetect($core, $oldResult);

        $core2 = $this->_makeCore();
        $newResult = new ConfigurationFetchResult(['flag' => 'off'], ConfigurationSource::CDN);
        $this->assertTrue($this->_callDetect($core2, $newResult));
    }

    public function testHashIsPersistedOnChange()
    {
        $core = $this->_makeCore();
        $result = new ConfigurationFetchResult(['flag' => 'on'], ConfigurationSource::CDN);

        $this->_callDetect($core, $result);

        $this->assertEquals(md5(json_encode(['flag' => 'on'])), $this->_cache->fetch(TEST_CACHE_KEY));
    }
}
