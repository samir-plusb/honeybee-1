<?php

namespace Honeybee\Agavi\User;

use Zend\Permissions\Acl;

/**
 * The ZendAclSecurityUser is responseable for detecting required scripts and deploying them for your view.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
class ZendAclSecurityUser extends \AgaviSecurityUser implements Acl\Role\RoleInterface
{
    const ALL_USERS_ID = NULL;

    protected $zendAcl;

    protected $accessConfig;

    public function initialize(\AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->accessConfig = include \AgaviConfigCache::checkConfig(
            \AgaviConfig::get('core.config_dir') . '/access_control.xml'
        );
        $this->zendAcl = $this->createZendAcl();
    }

    protected function createZendAcl()
    {
        $zendAcl = new Acl\Acl();
        // setup our resources
        foreach ($this->accessConfig['resources'] as $resource => $def)
        {
            $zendAcl->addResource($resource, $def['parent']);
            // deny all actions to all users per default to require explicit grants.
            foreach ($def['actions'] as $action)
            {
                $zendAcl->deny(self::ALL_USERS_ID, $resource, $action);
            }
        }

        // setup our roles
        foreach ($this->accessConfig['roles'] as $role => $def)
        {
            $zendAcl->addRole($role, $def['parent']);

            // apply all denies for the current role.
            foreach ($def['acl']['deny'] as $deny)
            {
                $operation = $deny['action'];
                $assertionTypeKey = $deny['constraint'];
                if (!isset($this->accessConfig['resource_actions'][$operation]))
                {
                    throw new \InvalidArgumentException("Undefined acl resource action '$operation' found in credential config!");
                }
                $resource = $this->accessConfig['resource_actions'][$operation];
                $zendAcl->deny($role, $resource, $operation, $this->createAssertion($assertionTypeKey));
            }
            
            // apply all grants for the current role.
            foreach ($def['acl']['grant'] as $grant)
            {
                $operation = $grant['action'];
                $assertionTypeKey = $grant['constraint'];

                if (!isset($this->accessConfig['resource_actions'][$operation]))
                {
                    throw new \InvalidArgumentException("Undefined acl resource action '$operation' found in credential config!");
                }
                $resource = $this->accessConfig['resource_actions'][$operation];

                $zendAcl->allow($role, $resource, $operation, $this->createAssertion($assertionTypeKey));
            }
        }

        return $zendAcl;
    }

    public function mapExternalRoleToDomain($origin, $role)
    {
        $roleKey = $origin . '::' . $role;
        if (! isset($this->accessConfig['external_roles'][$roleKey]))
        {
            return NULL;
        }
        return $this->accessConfig['external_roles'][$roleKey];
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
            return $this->getAttribute('acl_role');
        }
        return $this->getParameter('default_acl_role', 'user');
    }

    public function hasCredential($credential)
    {
        try
        {
            // @todo Probably non-executed code, check if it is executed and if not if we want it to execute ^^
            if ($credential instanceof Acl\Resource\ResourceInterface)
            {
                // an object instance was given; perform an access check on this (without an operation)
                // return $this->isAllowed($credential);
                return FALSE; // comment back in when we actually need this.
            }

            if (! is_scalar($credential))
            {
                // can't do much with this...
                return FALSE;
            }

            $splitCred = explode('::', $credential, 2);
            $resource = explode('.', $splitCred[0]);

            if (count($splitCred) == 2)
            {
                // a string like "product.create"; check the ACL
                return $this->isAllowed($resource[0], $credential);
            }
            else
            {
                // @todo Probably non-executed code, check if it is executed and if not if we want it to execute
                // something like "administrator"; let's see if that's our role or an ancestor of it
                // return $this->hasRole($credential);
                return FALSE; // comment back in when we actually need this.
            }
        }
        catch (\Exception $e)
        {
            // @todo Logging!
            return FALSE;
        }
    }

    protected function createAssertion($assertionClass = NULL)
    {
        $assertImplementor = $assertionClass;

        if (! $assertionClass)
        {
            return NULL;
        }

        if (! class_exists($assertImplementor))
        {
            $assertImplementor = implode('', array_map('ucfirst', explode('_', $assertionClass))) . 'Assertion';

            if (! class_exists($assertImplementor))
            {
                $assertImplementor = __NAMESPACE__ . '\\Assertions\\' . $assertImplementor;
            }
        }

        if (! class_exists($assertImplementor))
        {
            throw new \InvalidArgumentException(
                "Invalid assertion type given in acl configuration. Can not resolve to class: " . $assertImplementor
            );
        }

        return new $assertImplementor;
    }
}
