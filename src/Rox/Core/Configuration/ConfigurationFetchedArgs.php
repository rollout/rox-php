<?php

namespace Rox\Core\Configuration;

use DateTime;

class ConfigurationFetchedArgs
{
    /**
     * @var int $_fetcherStatus
     * @see FetcherStatus
     */
    private $_fetcherStatus;

    /**
     * @var DateTime $_creationDate
     */
    private $_creationDate;

    /**
     * @var bool $_hasChanges
     */
    private $_hasChanges;

    /**
     * @var int $_errorDetails
     * @see FetchedError
     */
    private $_errorDetails;

    /**
     * ConfigurationFetchedArgs constructor.
     * @param int $_errorDetails
     * @param int $_fetcherStatus
     * @param DateTime $_creationDate
     * @param bool $_hasChanges
     */
    public function __construct(
        $_errorDetails = FetchedError::NoError,
        $_fetcherStatus = FetcherStatus::ErrorFetchedFailed,
        DateTime $_creationDate = null,
        $_hasChanges = false)
    {
        $this->_fetcherStatus = $_fetcherStatus;
        $this->_creationDate = $_creationDate != null ? $_creationDate : new DateTime('0001-01-01');
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
     * @return DateTime
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
