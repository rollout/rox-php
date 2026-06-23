<?php

namespace Rox\Core\Network;

use GuzzleHttp\Psr7\Uri;
use Kevinrob\GuzzleCache\KeyValueHttpHeader;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rox\Core\Consts\PropertyType;

// NOTE: Using GreedyCacheStrategy as a base class here because
// our CDN prevents caching by using Cache Control headers.

class CdnCacheStrategy extends GreedyCacheStrategy
{
    protected function getCacheKey(RequestInterface $request, KeyValueHttpHeader $varyHeaders = null)
    {
        // Strip distinct_id from the URI before computing the cache key so that
        // per-instance identity doesn't cause cache misses across containers/pods.
        // The param is still sent in the actual HTTP request.
        $uri = Uri::withoutQueryValue($request->getUri(), PropertyType::getDistinctId()->getName());
        $request = $request->withUri($uri);
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

        return parent::getCacheObject($request, $response);
    }
}
