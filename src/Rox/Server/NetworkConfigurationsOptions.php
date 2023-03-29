<?php

namespace Rox\Server;


final class NetworkConfigurationsOptions
{
    /**
     * @var string
     */
    private $_getConfigApiEndpoint;

    /**
     * @var string
     */
    private $_getConfigCloudEndpoint;

    /**
     * @var string
     */
    private $_sendStateApiEndpoint;

    /**
     * @var string
     */
    private $_sendStateCloudEndpoint;

    /**
     * @var string
     */
    private $_errorReporterEndpoint;

    /**
     * NetworkConfigurationsOptions constructor.
     * @param NetworkConfigurationsOptions
     */
    public function __construct(
        $getConfigApiEndpoint,
        $getConfigCloudEndpoint,
        $sendStateApiEndpoint,
        $sendStateCloudEndpoint,
        $analyticsEndpoint,
        $errorReporterEndpoint = null)
    {
        $this->_getConfigApiEndpoint = $getConfigApiEndpoint;
        $this->_getConfigCloudEndpoint = $getConfigCloudEndpoint;
        $this->_sendStateApiEndpoint = $sendStateApiEndpoint;
        $this->_sendStateCloudEndpoint = $sendStateCloudEndpoint;
        $this->_analyticsEndpoint = $analyticsEndpoint;
        $this->_errorReporterEndpoint = $errorReporterEndpoint;
    }

    /**
     * @return string
     */
    function getConfigApiEndpoint()
    {
        return $this->_getConfigApiEndpoint;
    }

    /**
     * @return string
     */
    function getConfigCloudEndpoint()
    {
        return $this->_getConfigCloudEndpoint;
    }

    /**
     * @return string
     */
    function sendStateApiEndpoint()
    {
        return $this->_sendStateApiEndpoint;
    }

    /**
     * @return string
     */
    function sendStateCloudEndpoint()
    {
        return $this->_sendStateCloudEndpoint;
    }

    /**
     * @return string
     */
    function analyticsEndpoint()
    {
        return $this->_analyticsEndpoint;
    }

    /**
     * @return string
     */
    function errorReporterEndpoint()
    {
        return $this->_errorReporterEndpoint;
    }
}
