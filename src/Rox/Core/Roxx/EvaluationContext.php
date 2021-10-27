<?php

namespace Rox\Core\Roxx;

class EvaluationContext
{
    /**
     * @var bool $_shouldRaiseImpressionHandler
     */
    private $_shouldRaiseImpressionHandler;

    /**
     * @return bool
     */
    public function isShouldRaiseImpressionHandler()
    {
        return $this->_shouldRaiseImpressionHandler;
    }

    /**
     * @param bool $shouldRaiseImpressionHandler
     */
    public function setShouldRaiseImpressionHandler($shouldRaiseImpressionHandler)
    {
        $this->_shouldRaiseImpressionHandler = $shouldRaiseImpressionHandler;
    }
}