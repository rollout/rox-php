<?php

namespace Rox\Core\Network;

use Psr\Http\Message\ResponseInterface;

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
     * @return HttpResponseContentInterface
     */
    function getContent()
    {
        return new Psr7ResponseContentWrapper($this->_response->getBody());
    }
}
