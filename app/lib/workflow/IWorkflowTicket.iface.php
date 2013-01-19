<?php

interface IWorkflowTicket
{
    public function getWorkflowName();

    public function getWorkflowStep();

    public function getOwner();

    public function getStepCounts();

    public function getWaitUntil();

    public function getLastResult();

    public function reset();

    public function isReset();

    public function incrementStepCount();
}
