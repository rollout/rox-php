<?php

namespace Rox\Core\Network;

interface HttpResponseInterface
{
    const STATUS_OK = 200;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;

    /**
     * @return int
     */
    function getStatusCode();

    /**
     * @return bool
     */
    function isSuccessfulStatusCode();

    /**
     * @return HttpResponseContentInterface
     */
    function getContent();
}
