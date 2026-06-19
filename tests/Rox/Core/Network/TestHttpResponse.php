<?php

namespace Rox\Core\Network;

class TestHttpResponse extends AbstractHttpResponse
{
    /**
     * @var int $_status
     */
    private $_status;

    /**
     * @var string $_content
     */
    private $_content;

    /**
     * @var bool $_isFromCache
     */
    private $_isFromCache;

    /**
     * TestHttpResponse constructor.
     * @param int $status
     * @param string $content
     * @param bool $isFromCache
     */
    public function __construct($status, $content = "", $isFromCache = false)
    {
        $this->_status = $status;
        $this->_content = $content;
        $this->_isFromCache = $isFromCache;
    }

    /**
     * @return int
     */
    function getStatusCode()
    {
        return $this->_status;
    }

    /**
     * @return bool
     */
    function isFromCache()
    {
        return $this->_isFromCache;
    }

    /**
     * @return HttpResponseContentInterface
     */
    function getContent()
    {
        return new TestResponseContent($this->_content);
    }
}
