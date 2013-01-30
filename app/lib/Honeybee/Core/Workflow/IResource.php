<?php

namespace Honeybee\Core\Workflow;

interface IResource
{
    public function getWorkflowTicket();

    public function setWorkflowTicket($ticketData);

    public function getWorkflowConfigPath();
}
