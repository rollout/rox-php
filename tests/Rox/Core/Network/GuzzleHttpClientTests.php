<?php

namespace Rox\Core\Network;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\VolatileRuntimeStorage;
use Rox\RoxTestCase;

class GuzzleHttpClientTests extends RoxTestCase
{
    public function testSendGetLogsWarningWhenServingStaleCacheAfterLiveFetchFailure()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"ok":true}'),
            new ConnectException(
                'simulated network blip',
                new Request('GET', 'https://rox-conf.test/config')
            ),
        ]);

        // Negative TTL: the entry is stale the instant it's cached, no sleep needed.
        $strategy = new CdnCacheStrategy(new VolatileRuntimeStorage(), -1);

        $options = new GuzzleHttpClientOptions();
        $options->getHandlerStack()->setHandler($mock);
        $options->addMiddleware(new CacheMiddleware($strategy), 'cache');

        $client = new GuzzleHttpClient($options);

        // Populates the (already-stale) cache entry.
        $client->sendGet(new RequestData('https://rox-conf.test/config'));

        // Live fetch fails; the SDK falls back to the stale cache and should log about it,
        // even though isLogCacheHitsAndMisses() defaults to false.
        $client->sendGet(new RequestData('https://rox-conf.test/config'));

        $this->assertTrue(
            $this->_loggerFactory->getLogger()->hasWarningThatContains('setStaleIfErrorSeconds')
        );
    }

    public function testSendGetDoesNotLogHitByDefault()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"ok":true}'),
            new Response(200, [], '{"ok":true}'),
        ]);

        $strategy = new CdnCacheStrategy(new VolatileRuntimeStorage(), 3600);

        $options = new GuzzleHttpClientOptions();
        $options->getHandlerStack()->setHandler($mock);
        $options->addMiddleware(new CacheMiddleware($strategy), 'cache');

        $client = new GuzzleHttpClient($options);

        $client->sendGet(new RequestData('https://rox-conf.test/config'));
        $client->sendGet(new RequestData('https://rox-conf.test/config'));

        $this->assertFalse($this->_loggerFactory->getLogger()->hasDebugRecords());
        $this->assertFalse($this->_loggerFactory->getLogger()->hasWarningRecords());
    }
}
