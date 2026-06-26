<?php

namespace Rox\Core\Network;

abstract class AbstractHttpResponse implements HttpResponseInterface
{
    /**
     * @return bool
     */
    final function isSuccessfulStatusCode()
    {
        return $this->getStatusCode() >= 200 &&
            $this->getStatusCode() <= 299;
    }

    /**
     * @return string
     */
    function getCacheStatus()
    {
        return CacheStatus::MISS;
    }

    /**
     * @return bool
     */
    final function isFromCache()
    {
        return CacheStatus::isFromCache($this->getCacheStatus());
    }

    /**
     * @return bool
     */
    final function isContentUnchanged()
    {
        return CacheStatus::isContentUnchanged($this->getCacheStatus());
    }
}
