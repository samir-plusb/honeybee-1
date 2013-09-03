<?php

namespace Honeybee\Core\Dat0r;

use Honeybee\Core\Workflow\ITicket;

abstract class WorkflowTicket extends \Dat0r\Core\Document\Document implements ITicket
{
    public function reset()
    {
        $this->setWorkflowName(NULL);
        $this->setWorkflowStep(NULL);
        $this->setStepCounts(NULL);
    }

    public function isReset()
    {
        $workflowName = $this->getWorkflowName();
        $step = $this->getWorkflowStep();

        return empty($workflowName) || empty($step);
    }

    public function incrementStepCount()
    {
        $execCountMap = $this->getStepCounts();
        $stepName = $this->getWorkflowStep();

        if (empty($execCountMap))
        {
            $execCountMap = array($stepName => 1);
        }
        else
        {
            if (isset($execCountMap[$stepName]))
            {
                $execCountMap[$stepName]++;
            }
            else
            {
                $execCountMap[$stepName] = 1;
            }
        }

        $this->setStepCounts($execCountMap);

        return $execCountMap[$stepName];
    }
}
