<?php

namespace Rox\Core\Configuration;

use Rox\Core\Utils\TimeUtils;

class ConfigurationFetchedArgs
{
    /**
     * @var int $_fetcherStatus
     * @see FetcherStatus
     */
    private $_fetcherStatus;

    /**
     * @var float $_creationDate Creation timestamp in milliseconds
     */
    private $_creationDate;

    /**
     * @var bool $_hasChanges
     */
    private $_hasChanges;

    /**
     * @var int $_errorDetails
     * @see FetcherError
     */
    private $_errorDetails;

    /**
     * ConfigurationFetchedArgs constructor.
     * @param int $_errorDetails
     * @param int $_fetcherStatus
     * @param float|null $_creationDate
     * @param bool $_hasChanges
     */
    public function __construct(
        $_errorDetails = FetcherError::NoError,
        $_fetcherStatus = FetcherStatus::ErrorFetchedFailed,
        $_creationDate = null,
        $_hasChanges = false)
    {
        $this->_fetcherStatus = $_fetcherStatus;
        $this->_creationDate = $_creationDate != null ? $_creationDate : TimeUtils::currentTimeMillis();
        $this->_hasChanges = $_hasChanges;
        $this->_errorDetails = $_errorDetails;
    }

    /**
     * @return int
     */
    public function getFetcherStatus()
    {
        return $this->_fetcherStatus;
    }

    /**
     * @return float
     */
    public function getCreationDate()
    {
        return $this->_creationDate;
    }

    /**
     * @return bool
     */
    public function isHasChanges()
    {
        return $this->_hasChanges;
    }

    /**
     * @return int
     */
    public function getErrorDetails()
    {
        return $this->_errorDetails;
    }
}
