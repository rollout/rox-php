<?php

namespace Rox\Core\Network;

class RequestData
{
    /**
     * @var string $_url
     */
    private $_url;

    /**
     * @var array|null
     */
    private $_queryParams;

    /**
     * RequestData constructor.
     * @param string $url
     * @param array $queryParams
     */
    public function __construct($url, array $queryParams = null)
    {
        $this->_url = $url;
        $this->_queryParams = $queryParams;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @return array|null
     */
    public function getQueryParams()
    {
        return $this->_queryParams;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            'url' => $this->_url,
            'params' => $this->_queryParams
        ]);
    }
}
