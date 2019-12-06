<?php

namespace Rox\Core\Network;

class StringContent
{
    /**
     * @var string $_content
     */
    private $_content;

    /**
     * @var string $_contentType
     */
    private $_contentType;

    /**
     * @var string $_encoding
     */
    private $_encoding;

    /**
     * StringContent constructor.
     * @param string $content
     * @param string $contentType
     * @param string $encoding
     */
    public function __construct($content, $contentType, $encoding = 'UTF-8')
    {
        $this->_content = $content;
        $this->_contentType = $contentType;
        $this->_encoding = $encoding;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
}
