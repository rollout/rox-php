<?php

namespace Rox\Core\Repositories;

interface CustomPropertyEventHandlerInterface
{
    /**
     * @param CustomPropertyRepositoryInterface $sender
     * @param CustomPropertyAddedArgs $args
     */
    function onCustomPropertyAdded(
        CustomPropertyRepositoryInterface $sender,
        CustomPropertyAddedArgs $args);
}
