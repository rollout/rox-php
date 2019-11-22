<?php

namespace Rox\Core\Roxx;

class TokenType
{
    /**
     * @var string $_text
     */
    private $_text;

    /**
     * @var string $_pattern
     */
    private $_pattern;

    /**
     * TokenType constructor.
     * @param string $text
     * @param string $pattern
     */
    public function __construct($text, $pattern)
    {
        $this->_text = $text;
        $this->_pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_text;
    }
}
