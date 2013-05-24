<?php

/**
 * Handles the %system_actions.module_disabled% logic.
 */
class Default_ModuleDisabledAction extends DefaultBaseAction 
{
    public function getDefaultViewName() 
    {
        return 'Success';
    }

    public function isSecure()
    {
        return false;
    }
}

