<?php

namespace Rox\Core\Network;

use GuzzleHttp\HandlerStack;

class GuzzleHttpClientOptions
{
    /**
     * @var HandlerStack $_handlerStack
     */
    private $_handlerStack;

    /**
     * @var bool $_skipHostKeyVerification
     */
    private $_skipHostKeyVerification = true;

    /**
     * @var bool $_logCacheHitsAndMisses
     */
    private $_logCacheHitsAndMisses = false;

    /**
     * @var string[]
     */
    private $_noCachePaths = [];

    /**
     * GuzzleHttpClientOptions constructor.
     */
    public function __construct()
    {
        $this->_handlerStack = HandlerStack::create();
    }

    /**
     * @return HandlerStack
     */
    public function getHandlerStack()
    {
        return $this->_handlerStack;
    }

    /**
     * @return bool
     */
    public function isSkipHostKeyVerification()
    {
        return $this->_skipHostKeyVerification;
    }

    /**
     * @param callable $handler
     * @param string $name
     * @return GuzzleHttpClientOptions
     */
    public function addMiddleware(callable $handler, $name = '')
    {
        $this->_handlerStack->push($handler, $name);
        return $this;
    }

    /**
     * Sets whether to skip host key verification. By default,
     * it's set to <code>true</code> so no verification is performed.
     *
     * @param bool $skipHostKeyVerification
     * @return GuzzleHttpClientOptions
     */
    public function setSkipHostKeyVerification($skipHostKeyVerification)
    {
        $this->_skipHostKeyVerification = $skipHostKeyVerification;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLogCacheHitsAndMisses()
    {
        return $this->_logCacheHitsAndMisses;
    }

    /**
     * @param bool $logCacheHitsAndMisses
     * @return GuzzleHttpClientOptions
     */
    public function setLogCacheHitsAndMisses($logCacheHitsAndMisses)
    {
        $this->_logCacheHitsAndMisses = $logCacheHitsAndMisses;
        return $this;
    }

    /**
     * @return array
     */
    public function getNoCachePaths()
    {
        return $this->_noCachePaths;
    }

    /**
     * @param string[] $noCachePaths
     * @return GuzzleHttpClientOptions
     */
    public function setNoCachePaths(array $noCachePaths)
    {
        $this->_noCachePaths = $noCachePaths;
        return $this;
    }
}
