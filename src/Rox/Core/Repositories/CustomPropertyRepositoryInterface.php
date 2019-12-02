<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\CustomPropertyInterface;

interface CustomPropertyRepositoryInterface
{
    /**
     * @param CustomPropertyInterface $customProperty
     * @return void
     */
    function addCustomProperty($customProperty);

    /**
     * @param CustomPropertyInterface $customProperty
     * @return void
     */
    function addCustomPropertyIfNotExists($customProperty);

    /**
     * @param string $name
     * @return CustomPropertyInterface
     */
    function getCustomProperty($name);

    /**
     * @return array
     */
    function getAllCustomProperties();

    /**
     * @param CustomPropertyEventHandlerInterface $eventHandler
     */
    function addCustomPropertyEventHandler(CustomPropertyEventHandlerInterface $eventHandler);
}
