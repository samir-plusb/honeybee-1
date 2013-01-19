<?php

interface IWorkflowResource
{
    public function getWorkflowTicket();

    public function setWorkflowTicket($ticketData);

    public function getWorkflowConfigPath();
}
