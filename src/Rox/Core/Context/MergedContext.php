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
     * @param ContextInterface|null $globalContext
     * @param ContextInterface|null $localContext
     */
    public function __construct(
        ContextInterface $globalContext = null,
        ContextInterface $localContext = null)
    {
        $this->_globalContext = $globalContext;
        $this->_localContext = $localContext;
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
