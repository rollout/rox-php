<?php

namespace Rox\Core\Network;

use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// NOTE: Using GreedyCacheStrategy as a base class here because
// our CDN prevents caching by using Cache Control headers.

class CdnCacheStrategy extends GreedyCacheStrategy
{
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
