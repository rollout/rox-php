<?php

namespace Rox\Core\Network;

use DateTime;
use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\KeyValueHttpHeader;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// NOTE: Using GreedyCacheStrategy as a base class here because
// our CDN prevents caching by using Cache Control headers.

class CdnCacheStrategy extends GreedyCacheStrategy
{
    // How much longer, past its normal TTL, a cached config is still served if a live
    // fetch fails (transport error or 5xx from the CDN) - see SECO-5672 / CBP-58247.
    const STALE_IF_ERROR_SECONDS = 86400;

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
            ->setTimestamp($staleAt->getTimestamp() + self::STALE_IF_ERROR_SECONDS);

        return new CacheEntry(
            $cacheEntry->getOriginalRequest(),
            $cacheEntry->getOriginalResponse(),
            $staleAt,
            $staleIfErrorTo
        );
    }
}
