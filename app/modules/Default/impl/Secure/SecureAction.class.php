<?php

/**
 * Handles the %system_actions.secure% action logic which is executed by the
 * \AgaviSecurityFilter as soon as an action that is marked as secure is
 * encountered without the having an authenticated user session.
 */
class Default_SecureAction extends DefaultBaseAction 
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
