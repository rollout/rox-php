<?php

namespace Rox\Core\Network;

use Psr\Http\Message\StreamInterface;

class Psr7ResponseContentWrapper implements HttpResponseContentInterface
{
    /**
     * @var StreamInterface
     */
    private $_stream;

    /**
     * Psr7ResponseContentWrapper constructor.
     * @param StreamInterface $_stream
     */
    public function __construct(StreamInterface $_stream)
    {
        $this->_stream = $_stream;
    }

    /**
     * @return string
     */
    function readAsString()
    {
        return $this->_stream->getContents();
    }
}
