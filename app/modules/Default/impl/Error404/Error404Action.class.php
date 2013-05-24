<?php

/**
 * Handles the %system_actions.error_404% logic.
 */
class Default_Error404Action extends DefaultBaseAction 
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
