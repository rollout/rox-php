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
        $exp = array_filter($this->_experiments, function (ExperimentModel $e) use ($flagName) {
            $index = array_search($flagName, $e->getFlags());
            return $index !== false && $index >= 0;
        });
        return count($exp) > 0 ? $exp[0] : null;
    }

    /**
     * @return ExperimentModel[]
     */
    function getAllExperiments()
    {
        return $this->_experiments;
    }
}
