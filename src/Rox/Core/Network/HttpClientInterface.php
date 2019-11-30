<?php

namespace Rox\Core\Network;

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP client abstraction. Would help to avoid
 * direct dependencies on Guzzle for example.
 *
 * @package Rox\Core\Network
 */
interface HttpClientInterface
{
    /**
     * @param RequestData $requestData
     * @return ResponseInterface
     */
    function sendGet(RequestData $requestData);

    /**
     * @param RequestData $requestData
     * @return ResponseInterface
     */
    function sendPost(RequestData $requestData);

    /**
     * @param string $uri
     * @param StringContent $content
     * @return ResponseInterface
     */
    function postContent($uri, StringContent $content);
}