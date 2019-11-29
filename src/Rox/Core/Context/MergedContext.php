<?php

namespace Rox\Core\Context;

class MergedContext implements ContextInterface
{
    /**
     * @var ContextInterface $_globalContext
     */
    private $_globalContext;

    /**
     * @var ContextInterface $_localContext
     */
    private $_localContext;

    /**
     * MergedContext constructor.
     * @param ContextInterface|null $_globalContext
     * @param ContextInterface|null $_localContext
     */
    public function __construct($_globalContext, $_localContext)
    {
        $this->_globalContext = $_globalContext;
        $this->_localContext = $_localContext;
    }

    /**
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        if ($this->_localContext != null && $this->_localContext->get($key) != null) {
            return $this->_localContext->get($key);
        } else if ($this->_globalContext != null) {
            return $this->_globalContext->get($key);
        } else {
            return null;
        }
    }
}
