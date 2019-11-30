<?php

namespace Rox\Core\Network;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

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
        $this->_client = new Client();
    }

    /**
     * @param RequestData $requestData
     * @return ResponseInterface
     */
    function sendGet(RequestData $requestData)
    {
        try {
            $uri = new Uri($requestData);
            if ($requestData->getQueryParams() == null) {
                $uri = Uri::withQueryValues($uri, $requestData->getQueryParams());
            }
            return $this->_client->send(new Request('GET', $uri));
        } catch (GuzzleException $e) {
            throw new HttpClientException("Failed to send GET request ${requestData}", $e);
        }
    }

    /**
     * @param RequestData $requestData
     * @return ResponseInterface
     */
    function sendPost(RequestData $requestData)
    {
        return $this->postContent($requestData->getUrl(), new StringContent(
            json_encode($requestData->getQueryParams()),
            'application/json',
            'URF-8'
        ));
    }

    /**
     * @param string $uri
     * @param StringContent $content
     * @return ResponseInterface
     */
    function postContent($uri, StringContent $content)
    {
        try {
            $uriToSend = new Uri($uri);
            $request = new Request('POST', $uriToSend, [
                'Content-Type' => $content->getContentType() . '; charset=' . $content->getEncoding()
            ]);
            $request->withBody(\GuzzleHttp\Psr7\stream_for($content->getContent()));
            return $this->_client->send($request);
        } catch (GuzzleException $e) {
            throw new HttpClientException("Failed to send POST request to ${uri}", $e);
        }
    }
}
