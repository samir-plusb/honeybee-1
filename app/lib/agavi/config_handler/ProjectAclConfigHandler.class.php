<?php

/**
 * ProjectAclConfigHandler parses configuration files that follow the midas access_control markup.
 *
 * @package    Project
 * @subpackage Config
 *
 * @author     Thorsten Schmitt-Rink
 * @copyright  The Agavi Project
 *
 * @version    $Id:$
 */
class ProjectAclConfigHandler extends AgaviXmlConfigHandler
{
    const XML_NAMESPACE = 'http://berlinonline.de/schemas/midas/config/access_control/1.0';

    /**
     * Execute this configuration handler.
     *
     * @param      string An absolute filesystem path to a configuration file.
     * @param      string An optional context in which we are currently running.
     *
     * @return     string Data to be written to a cache file.
     *
     * @throws     <b>AgaviUnreadableException</b> If a requested configuration
     *                                             file does not exist or is not
     *                                             readable.
     * @throws     <b>AgaviParseException</b> If a requested configuration file is
     *                                        improperly formatted.
     */
    public function execute(AgaviXmlConfigDomDocument $document)
    {
        $document->setDefaultNamespace(self::XML_NAMESPACE, 'acl');
        $config = $document->documentURI;
        $data = array();
        $parsedResources = array();
        $parsedRoles = array();
        $resourceActions = array();
        $externalRoles = array();
        /* @var $cfgNode AgaviXmlConfigDomElement */
        foreach ($document->getConfigurationElements() as $cfgNode)
        {
            // parse resources
            $resourcesNode = $cfgNode->getChild('resources');
            foreach ($resourcesNode->get('resource') as $resourceNode)
            {
                $resource = $resourceNode->getAttribute('name');
                // parse actions
                $actionsNode = $resourceNode->getChild('actions');
                $actions = array();
                if ($actionsNode)
                {
                    foreach ($actionsNode->get('action') as $actionNode)
                    {
                        $action = $actionNode->nodeValue;
                        $actions[] = $action;
                        // setup a reverse action => resource lookup map.
                        $resourceActions[$action] = $resource;
                    }
                }
                $parsedResources[$resource] = array(
                    'description' => $resourceNode->getChild('description')->nodeValue,
                    'actions' => $actions,
                    'parent' => $resourceNode->getAttribute('parent', NULL)
                );
            }

            // parse roles
            $rolesNode = $cfgNode->getChild('roles');
            foreach ($rolesNode->get('role') as $roleNode)
            {
                // parse members
                $members = array();
                $role = $roleNode->getAttribute('name');
                $membersNode = $roleNode->getChild('members');
                if ($membersNode)
                {
                    foreach ($membersNode->get('member') as $memberNode)
                    {
                        $externalRole = sprintf(
                            '%s::%s',
                            $memberNode->getAttribute('type'),
                            $memberNode->nodeValue
                        );
                        $externalRoles[$externalRole] = $role;
                        $members[] = array(
                            'type' => $memberNode->getAttribute('type'),
                            'name' => $memberNode->nodeValue
                        );
                    }
                }

                // parse acl
                $acl = array(
                    'grant' => array(),
                    'deny' => array()
                );
                $aclNode = $roleNode->getChild('acl');
                if ($aclNode)
                {
                    foreach ($aclNode->get('grant') as $grantNode)
                    {
                        $acl['grant'][] = array(
                            'action' => $grantNode->nodeValue,
                            'constraint' => $grantNode->getAttribute('if', NULL)
                        );
                    }
                    foreach ($aclNode->get('deny') as $denyNode)
                    {
                        $acl['deny'][] = array(
                            'action' => $denyNode->nodeValue,
                            'constraint' => $denyNode->getAttribute('if', NULL)
                        );
                    }
                }
                $parsedRoles[$role] = array(
                    'description' => $roleNode->getChild('description')->nodeValue,
                    'members' => $members,
                    'acl' => $acl,
                    'parent' => $resourceNode->getAttribute('parent', NULL)
                );
            }
        }

        $data['roles'] = $parsedRoles;
        $data['resources'] = $parsedResources;
        $data['resource_actions'] = $resourceActions;
        $data['external_roles'] = $externalRoles;
        $configCode = sprintf('return %s;', var_export($data, TRUE));
        return $this->generate($configCode, $config);
    }

}

?>
