<?php

namespace Rox\Core\Network;

use Psr\Http\Message\ResponseInterface;
use Rox\Core\Network\CacheStatus;

class Psr7ResponseWrapper extends AbstractHttpResponse
{
    /**
     * @var ResponseInterface
     */
    private $_response;

    /**
     * Psr7ResponseWrapper constructor.
     * @param ResponseInterface $_response
     */
    public function __construct(ResponseInterface $_response)
    {
        $this->_response = $_response;
    }

    function getStatusCode()
    {
        return $this->_response->getStatusCode();
    }

    /**
     * @return string
     */
    function getCacheStatus()
    {
        $header = $this->_response->getHeader('X-Kevinrob-Cache');
        $value = !empty($header) ? $header[0] : CacheStatus::MISS;
        return in_array($value, [CacheStatus::HIT, CacheStatus::REVALIDATED, CacheStatus::STALE], true)
            ? $value
            : CacheStatus::MISS;
    }

    /**
     * @return HttpResponseContentInterface
     */
    function getContent()
    {
        return new Psr7ResponseContentWrapper($this->_response->getBody());
    }
}
