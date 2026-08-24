<?php

namespace Rox\Core\Network;

use DateTime;
use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\KeyValueHttpHeader;
use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// NOTE: Using GreedyCacheStrategy as a base class here because
// our CDN prevents caching by using Cache Control headers.

class CdnCacheStrategy extends GreedyCacheStrategy
{
    // Default grace window, past a cache entry's normal TTL, during which a stale config is
    // still served if a live fetch fails (transport error or 5xx from the CDN). Sized around the
    // CDN blips actually observed (short, infrequent) rather than a worst-case outage. Overridable
    // via RoxOptionsBuilder::setStaleIfErrorSeconds().
    const DEFAULT_STALE_IF_ERROR_SECONDS = 1800;

    /**
     * @var int
     */
    private $_staleIfErrorSeconds;

    /**
     * @param CacheStorageInterface|null $cache
     * @param int $defaultTtl
     * @param KeyValueHttpHeader|null $varyHeaders
     * @param int|null $staleIfErrorSeconds
     */
    public function __construct(CacheStorageInterface $cache = null, $defaultTtl, KeyValueHttpHeader $varyHeaders = null, $staleIfErrorSeconds = null)
    {
        parent::__construct($cache, $defaultTtl, $varyHeaders);
        $this->_staleIfErrorSeconds = $staleIfErrorSeconds !== null ? $staleIfErrorSeconds : self::DEFAULT_STALE_IF_ERROR_SECONDS;
    }

    protected function getCacheKey(RequestInterface $request, KeyValueHttpHeader $varyHeaders = null)
    {
        // Key on path only so that per-instance query params (e.g. distinct_id)
        // don't cause cache misses across containers/pods.
        $request = $request->withUri($request->getUri()->withQuery(''));
        return parent::getCacheKey($request, $varyHeaders);
    }

    protected function getCacheObject(RequestInterface $request, ResponseInterface $response)
    {
        if ($response->getStatusCode() == 200) {
            $contents = $response->getBody()->getContents();
            $response->getBody()->rewind();
            $json = json_decode($contents, true);
            if ($json && isset($json['result']) && intval($json['result']) === 404) {
                return null;
            }
        }

        $cacheEntry = parent::getCacheObject($request, $response);
        if ($cacheEntry === null) {
            return null;
        }

        $staleAt = $cacheEntry->getStaleAt();
        $staleIfErrorTo = (new DateTime('@'.$staleAt->getTimestamp()))
            ->setTimestamp($staleAt->getTimestamp() + $this->_staleIfErrorSeconds);

        return new CacheEntry(
            $cacheEntry->getOriginalRequest(),
            $cacheEntry->getOriginalResponse(),
            $staleAt,
            $staleIfErrorTo
        );
    }
}
