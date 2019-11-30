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
     * TestHttpResponse constructor.
     * @param int $status
     * @param string $content
     */
    public function __construct($status, $content = "")
    {
        $this->_status = $status;
        $this->_content = $content;
    }

    /**
     * @return int
     */
    function getStatusCode()
    {
        return $this->_status;
    }

    /**
     * @return HttpResponseContentInterface
     */
    function getContent()
    {
        return new TestResponseContent($this->_content);
    }
}
