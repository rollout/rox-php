<?php

namespace Rox\Core\Network;

use Psr\Http\Message\ResponseInterface;
use Rox\Core\Configuration\ConfigurationFetcherOneSource;
use Rox\Core\Consts\Environment;

final class ConfigurationFetcherRoxy extends ConfigurationFetcherOneSource
{
    /**
     * @return int
     */
    protected function getSource()
    {
        return ConfigurationSource::Roxy;
    }

    /**
     * @return ResponseInterface
     */
    protected function internalFetch()
    {
        $roxyEndPoint = $this->_url . '/' . Environment::getRoxyInternalPath();
        $queryParams = $this->_deviceProperties->getAllProperties();
        $roxyRequest = new RequestData($roxyEndPoint, $queryParams);
        return $this->_request->sendGet($roxyRequest);
    }
}
