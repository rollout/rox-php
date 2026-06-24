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
     * @return bool
     */
    function isFromCache()
    {
        return false;
    }
}
