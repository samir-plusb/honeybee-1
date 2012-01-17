<?php

class UserIsItemOwnerAssertion implements Zend_Acl_Assert_Interface
{
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = NULL, Zend_Acl_Resource_Interface $resource = NULL, $privilege = NULL)
	{
		if(!($resource instanceof IWorkflowItem))
        {
			// in case the check is performed without a specific workflow-item instance:
			// let's assume that the user can edit a generic workflow-item.
			return FALSE;
		}

		if(!($role instanceof AgaviUser))
        {
			// in case the check is performed without a specific user instance:
			// let's assume that any generic user cannot edit this workflow-item.
			return FALSE;
		}
		return $resource->getOwnerName() == $role->getAttribute('login');
	}
}

?>
