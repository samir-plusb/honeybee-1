<?php

class ProjectUserMayDoNothingAssertion implements Zend_Acl_Assert_Interface
{
    /**
     *
     * @param Zend_Acl $acl
     * @param Zend_Acl_Role_Interface $role
     * @param Zend_Acl_Resource_Interface $resource
     * @param string $privilege
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function assert(Zend_Acl $acl,
        Zend_Acl_Role_Interface $role = NULL, Zend_Acl_Resource_Interface $resource = NULL, $privilege = NULL)
    {
        /*if (!($resource instanceof ProductModel))
        {

        }
        if (!($role instanceof AgaviUser))
        {

        }*/
        return FALSE;
    }
}

?>