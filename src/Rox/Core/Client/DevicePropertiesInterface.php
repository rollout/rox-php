<?php

namespace Rox\Core\Client;

interface DevicePropertiesInterface
{
    /**
     * @return array
     */
    function getAllProperties();

    /**
     * @return string
     */
    function getLibVersion();

    /**
     * @return string
     */
    function getDistinctId();

    /**
     * @return string
     */
    function getRolloutKey();
}