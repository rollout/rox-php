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
     * @var string $_cacheStatus
     */
    private $_cacheStatus;

    /**
     * TestHttpResponse constructor.
     * @param int $status
     * @param string $content
     * @param string $cacheStatus
     * @see CacheStatus
     */
    public function __construct($status, $content = "", $cacheStatus = CacheStatus::MISS)
    {
        $this->_status = $status;
        $this->_content = $content;
        $this->_cacheStatus = $cacheStatus;
    }

    /**
     * @return int
     */
    function getStatusCode()
    {
        return $this->_status;
    }

    /**
     * @return string
     */
    function getCacheStatus()
    {
        return $this->_cacheStatus;
    }

    /**
     * @return HttpResponseContentInterface
     */
    function getContent()
    {
        return new TestResponseContent($this->_content);
    }
}
