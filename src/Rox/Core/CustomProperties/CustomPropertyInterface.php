<?php

namespace Rox\Core\CustomProperties;

/**
 * @package Rox\Core\CustomProperties
 */
interface CustomPropertyInterface
{
    /**
     * @return string
     */
    function getName();

    /**
     * @return CustomPropertyType
     */
    function getType();

    /**
     * @return callable
     */
    function getValue();
}
