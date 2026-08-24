<?php

namespace Rox\Core\Network;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\VolatileRuntimeStorage;
use Rox\RoxTestCase;

/**
 * SECO-5672: once a cached CDN response goes stale, the SDK should fall back
 * to it on a transport failure or 5xx from the CDN edge - the same way a
 * fresh AppliedFromLocalStorage fetch would - instead of failing outright and
 * dropping flags to their constructor defaults.
 *
 * CdnCacheStrategy never sets a stale-if-error window on the CacheEntry it
 * builds, so the vendor cache middleware's stale-on-error fallback (which it
 * otherwise fully supports) never triggers. These tests reproduce that gap
 * directly against CdnCacheStrategy + the real Guzzle cache middleware, with
 * no network involved (Guzzle's MockHandler stands in for the CDN).
 */
class CdnCacheStrategyTests extends RoxTestCase
{
    /**
     * @param \Exception|Response $secondResponse
     * @param int|null $staleIfErrorSeconds
     * @return Client
     */
    private function _clientWithStaleCacheAndFailingSecondRequest($secondResponse, $staleIfErrorSeconds = null)
    {
        $storage = new VolatileRuntimeStorage();
        // Negative TTL: the entry is stale the instant it's cached, no sleep needed.
        $strategy = new CdnCacheStrategy($storage, -1, null, $staleIfErrorSeconds);

        $mock = new MockHandler([
            new Response(200, [], '{"ok":true}'),
            $secondResponse,
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(new CacheMiddleware($strategy), 'cache');

        return new Client(['handler' => $handlerStack]);
    }

    public function testShouldServeStaleResponseWhenLiveRequestFailsWithConnectionError()
    {
        $client = $this->_clientWithStaleCacheAndFailingSecondRequest(
            new ConnectException(
                'simulated network blip',
                new Request('GET', 'https://rox-conf.test/config')
            )
        );

        // Populates the (already-stale) cache entry.
        $first = $client->get('https://rox-conf.test/config');
        $this->assertEquals('{"ok":true}', (string) $first->getBody());

        // The cache entry is stale, so Guzzle attempts a live request, which fails
        // at the transport level. Expectation: the SDK falls back to the stale
        // cached response instead of the failure propagating.
        $second = $client->get('https://rox-conf.test/config');
        $this->assertEquals('{"ok":true}', (string) $second->getBody());
    }

    public function testShouldServeStaleResponseWhenCdnReturns5xx()
    {
        $client = $this->_clientWithStaleCacheAndFailingSecondRequest(
            new Response(500, [], 'simulated CDN/edge failure')
        );

        $first = $client->get('https://rox-conf.test/config');
        $this->assertEquals('{"ok":true}', (string) $first->getBody());

        $second = $client->get('https://rox-conf.test/config');
        $this->assertEquals('{"ok":true}', (string) $second->getBody());
    }

    public function testShouldNotServeStaleResponseOnceStaleIfErrorWindowHasElapsed()
    {
        // staleIfErrorSeconds=0: the grace window is exhausted the instant the entry
        // goes stale, so the fallback should never kick in and the failure should propagate.
        $client = $this->_clientWithStaleCacheAndFailingSecondRequest(
            new ConnectException(
                'simulated network blip',
                new Request('GET', 'https://rox-conf.test/config')
            ),
            0
        );

        $first = $client->get('https://rox-conf.test/config');
        $this->assertEquals('{"ok":true}', (string) $first->getBody());

        $this->expectException(ConnectException::class);
        $client->get('https://rox-conf.test/config');
    }
}
