<?php

namespace Rox\Core\Network;

use Rox\RoxTestCase;

class CacheStatusTests extends RoxTestCase
{
    public function testIsFromCache()
    {
        $this->assertTrue(CacheStatus::isFromCache(CacheStatus::HIT));
        $this->assertTrue(CacheStatus::isFromCache(CacheStatus::STALE));
        $this->assertFalse(CacheStatus::isFromCache(CacheStatus::MISS));
        $this->assertFalse(CacheStatus::isFromCache(CacheStatus::REVALIDATED));
    }

    public function testIsContentUnchanged()
    {
        $this->assertTrue(CacheStatus::isContentUnchanged(CacheStatus::HIT));
        $this->assertTrue(CacheStatus::isContentUnchanged(CacheStatus::REVALIDATED));
        $this->assertTrue(CacheStatus::isContentUnchanged(CacheStatus::STALE));
        $this->assertFalse(CacheStatus::isContentUnchanged(CacheStatus::MISS));
    }
}
