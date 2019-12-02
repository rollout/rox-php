<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\CustomPropertyInterface;

class CustomPropertyAddedArgs
{
    /**
     * @var CustomPropertyInterface $_customProperty
     */
    private $_customProperty;

    /**
     * CustomPropertyAddedArgs constructor.
     * @param CustomPropertyInterface $_customProperty
     */
    public function __construct(CustomPropertyInterface $_customProperty)
    {
        $this->_customProperty = $_customProperty;
    }

    /**
     * @return CustomPropertyInterface
     */
    public function getCustomProperty()
    {
        return $this->_customProperty;
    }
}
