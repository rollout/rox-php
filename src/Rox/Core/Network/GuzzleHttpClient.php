<?php

namespace Rox\Core\Network;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;

class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $_client;

    /**
     * GuzzleHttpClient constructor.
     */
    public function __construct()
    {
        $this->_client = new Client(['verify' => false]);
    }

    /**
     * @param RequestData $requestData
     * @return HttpResponseInterface
     */
    function sendGet(RequestData $requestData)
    {
        try {
            $uri = new Uri($requestData->getUrl());
            if ($requestData->getQueryParams() != null) {
                $uri = Uri::withQueryValues($uri, $requestData->getQueryParams());
            }
            return new Psr7ResponseWrapper($this->_client->send(new Request('GET', $uri)));
        } catch (GuzzleException $e) {
            throw new HttpClientException("Failed to send GET request ${requestData}", $e);
        }
    }

    /**
     * @param RequestData $requestData
     * @return HttpResponseInterface
     */
    function sendPost(RequestData $requestData)
    {
        return $this->postJson($requestData->getUrl(), $requestData->getQueryParams());
    }

    /**
     * @param string $uri
     * @param array $data
     * @return HttpResponseInterface
     */
    function postJson($uri, array $data)
    {
        try {
            $uriToSend = new Uri($uri);
            return new Psr7ResponseWrapper($this->_client->post($uriToSend, [
                RequestOptions::JSON => $data
            ]));
        } catch (GuzzleException $e) {
            throw new HttpClientException("Failed to send POST request to ${uri}", $e);
        }
    }
}
