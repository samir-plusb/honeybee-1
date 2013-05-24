<?php

/**
 * Handles the %system_actions.unavailable% logic (in case of maintenance).
 */
class Default_UnavailableAction extends DefaultBaseAction
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

