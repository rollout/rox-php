<?php

namespace Rox\Core\Network;

use GuzzleHttp\Psr7\Response;
use Rox\RoxTestCase;

class Psr7ResponseWrapperTests extends RoxTestCase
{
    public function testHitIsRecognized()
    {
        $wrapper = new Psr7ResponseWrapper(new Response(200, ['X-Kevinrob-Cache' => CacheStatus::HIT]));
        $this->assertEquals(CacheStatus::HIT, $wrapper->getCacheStatus());
    }

    public function testRevalidatedIsRecognized()
    {
        $wrapper = new Psr7ResponseWrapper(new Response(200, ['X-Kevinrob-Cache' => CacheStatus::REVALIDATED]));
        $this->assertEquals(CacheStatus::REVALIDATED, $wrapper->getCacheStatus());
    }

    public function testStaleIsRecognized()
    {
        $wrapper = new Psr7ResponseWrapper(new Response(200, ['X-Kevinrob-Cache' => CacheStatus::STALE]));
        $this->assertEquals(CacheStatus::STALE, $wrapper->getCacheStatus());
    }

    public function testMissingHeaderIsMiss()
    {
        $wrapper = new Psr7ResponseWrapper(new Response(200));
        $this->assertEquals(CacheStatus::MISS, $wrapper->getCacheStatus());
    }

    public function testUnrecognizedHeaderValueIsMiss()
    {
        $wrapper = new Psr7ResponseWrapper(new Response(200, ['X-Kevinrob-Cache' => 'something-unexpected']));
        $this->assertEquals(CacheStatus::MISS, $wrapper->getCacheStatus());
    }
}
