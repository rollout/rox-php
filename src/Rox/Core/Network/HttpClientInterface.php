<?php

namespace Rox\Core\Network;

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
     * @return HttpResponseInterface
     */
    function sendGet(RequestData $requestData);

    /**
     * @param RequestData $requestData
     * @return HttpResponseInterface
     */
    function sendPost(RequestData $requestData);

    /**
     * @param string $uri
     * @param array $data
     * @return HttpResponseInterface
     */
    function postJson($uri, array $data);
}
