<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\ExperimentModel;

class ExperimentRepository implements ExperimentRepositoryInterface
{
    /**
     * @var ExperimentModel[] $_experiments
     */
    private $_experiments = [];

    /**
     * @param ExperimentModel[] $experiments
     * @return void
     */
    function setExperiments($experiments)
    {
        $this->_experiments = $experiments;
    }

    /**
     * @param string $flagName
     * @return ExperimentModel|null
     */
    function getExperimentByFlag($flagName)
    {
        return array_filter($this->_experiments, function (ExperimentModel $e) use ($flagName) {
            return array_search($flagName, $e->getFlags()) >= 0;
        })[0];
    }

    /**
     * @return ExperimentModel[]
     */
    function getAllExperiments()
    {
        return $this->_experiments;
    }
}
