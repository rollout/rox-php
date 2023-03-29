<?php

namespace Rox\Core\Consts;

class Environment
{
    private $_getConfigCDNPath;
    private $_getConfigAPIPath;
    private $_sendStateCDNPath;
    private $_sendStateAPIPath;
    private $_analyticsPath;
    private $_errorReporterPath;
    private $_name;

    const PRODUCTION = 'PRODUCTION';
    const CUSTOM = 'CUSTOM';

    public function __construct($options = null)
    {
        if ($options != null && $options->getNetworkConfigurationsOptions() != null) {
            $networkConfig = $options->getNetworkConfigurationsOptions();
            $this->_getConfigCDNPath = $this->chopLastSlash($networkConfig->getConfigCloudEndpoint());
            $this->_getConfigAPIPath = $this->chopLastSlash($networkConfig->getConfigApiEndpoint());
            $this->_sendStateCDNPath = $this->chopLastSlash($networkConfig->sendStateCloudEndpoint());
            $this->_sendStateAPIPath = $this->chopLastSlash($networkConfig->sendStateApiEndpoint());
            $this->_analyticsPath = $this->chopLastSlash($networkConfig->analyticsEndpoint());
            $this->_errorReporterPath = $this->chopLastSlash($networkConfig->errorReporterEndpoint());
            $this->_name = self::CUSTOM;
            return;
        }

        if ($options != null && $options->getRoxyURL() != null) {
            $this->_getConfigCDNPath = null;
            $this->_getConfigAPIPath = $this->chopLastSlash($options->getRoxyURL()) . '/' . $this->getRoxyInternalPath();
            $this->_sendStateCDNPath = null;
            $this->_sendStateAPIPath = null;
            $this->_analyticsPath = null;
            $this->_errorReporterPath = null;
            $this->_name = self::CUSTOM;
            return;
        }

        $this->_getConfigCDNPath = 'https://conf.rollout.io';
        $this->_getConfigAPIPath = 'https://x-api.rollout.io/device/get_configuration';
        $this->_sendStateCDNPath = 'https://statestore.rollout.io';
        $this->_sendStateAPIPath = 'https://x-api.rollout.io/device/update_state_store';
        $this->_analyticsPath = 'https://analytic.rollout.io';
        $this->_errorReporterPath = 'https://notify.bugsnag.com';
        $this->_name = self::PRODUCTION;
    }
    
    /**
     * @return string
     */
    private function chopLastSlash($url)
    {
        if ($url != null)
        {
            if (substr($url, -1) == '/')
            {
                return substr($url, 0, -1);
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getRoxyInternalPath()
    {
        return 'device/request_configuration';
    }

    /**
     * @return string
     */
    public function getConfigCDNPath()
    {
        return $this->_getConfigCDNPath;
    }

    /**
     * @return string
     */
    public function getConfigAPIPath()
    {
        return $this->_getConfigAPIPath;
    }

    /**
     * @return string
     */
    public function sendStateCDNPath()
    {
        return $this->_sendStateCDNPath;
    }

    /**
     * @return string
     */
    public function sendStateAPIPath()
    {
        return $this->_sendStateAPIPath;
    }

    /**
     * @return string
     */
    public function analyticsPath()
    {
        return $this->_analyticsPath;
    }

    /**
     * @return string
     */
    public function errorReporterPath()
    {
        return $this->_errorReporterPath;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->_name;
    }
}
