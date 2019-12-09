<?php

namespace Rox\Core\Network;

class GuzzleHttpClientFactory implements HttpClientFactoryInterface
{
    /**
     * @var GuzzleHttpClientOptions $_options
     */
    private $_options;

    /**
     * GuzzleHttpClientFactory constructor.
     * @param GuzzleHttpClientOptions|null $options
     */
    public function __construct($options = null)
    {
        $this->_options = $options ?: new GuzzleHttpClientOptions();
    }

    /**
     * @inheritDoc
     */
    function createHttpClient()
    {
        return new GuzzleHttpClient($this->_options);
    }
}
