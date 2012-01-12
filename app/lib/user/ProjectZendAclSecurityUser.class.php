<?php

class ProjectZendAclSecurityUser extends AgaviSecurityUser implements Zend_Acl_Role_Interface
{
    protected $zendAcl;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->zendAcl = $this->createZendAcl();
    }

    protected function createZendAcl()
    {
        $zendAcl = new Zend_Acl();

        // add the roles we'll be using to act on our resources
        $zendAcl->addRole('user')
                ->addRole('editor', 'user')
                ->addRole('cvd', 'editor')
                ->addRole('admin', 'cvd');

        // add the resources we will be acting on
        $zendAcl->addResource('workflow-item');

        // lets deny everything to have a nice secure basic setup
        $zendAcl->deny(null, 'workflow-item', 'read')
                ->deny(null, 'workflow-item', 'write');

        // then start giving the specific credentials to designated roles
        $zendAcl->allow('user', 'workflow-item', 'read')
                ->allow('editor', 'workflow-item', 'write', new ProjectIsWorkflowItemOwnerAssertion());

        return $zendAcl;
    }

    public function getZendAcl()
    {
        return $this->zendAcl;
    }

    public function isAllowed($resource, $operation = NULL)
    {
        return $this->getZendAcl()->isAllowed($this, $resource, $operation);
    }

    public function hasRole($role)
    {
        // could be our role directly, could be an ancestor, so check both
        return $this->getRoleId() == $role || $this->getZendAcl()->inheritsRole($this->getRoleId(), $role);
    }

    public function getRoleId()
    {
        if ($this->isAuthenticated() && $this->hasAttribute('acl_role'))
        {
            $role = $this->getAttribute('acl_role');
        }
        $role = $this->getParameter('default_acl_role', 'user');
        return $role;
    }

    /**
     *
     * @param mixed $credential
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hasCredential($credential)
    {
        try
        {
            if ($credential instanceof Zend_Acl_Resource_Interface)
            {
                // an object instance was given; perform an access check on this (without an operation)
                return $this->isAllowed($credential);
            }

            if (!is_scalar($credential))
            {
                // can't do much with this...
                return FALSE;
            }

            $credential = explode('.', $credential, 2);
            if (count($credential) == 2)
            {
                // a string like "product.create"; check the ACL
                return $this->isAllowed($credential[0], $credential[1]);
            }
            else
            {
                // something like "administrator"; let's see if that's our role or an ancestor of it
                return $this->hasRole($credential[0]);
            }
        }
        catch (Zend_Acl_Exception $e)
        {
            return FALSE;
        }
    }
}

?>
