<?php

use Honeybee\Core\Dat0r\Module;
use Honeybee\Domain\User\UserModule;
use Honeybee\Agavi\Routing\ModuleRoutingCallback;
use Honeybee\Agavi\Action\BaseAction;

/**
 * The base action from which all User actions inherit from.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 * @subpackage      Agavi/Action
 */
class UserBaseAction extends BaseAction
{
    protected function getModule()
    {
        $module = $this->getContext()->getRequest()->getAttribute('module', ModuleRoutingCallback::ATTRIBUTE_NAMESPACE);

        if (!($module instanceof Module))
        {
            $module = UserModule::getInstance();
        }

        if (!($module instanceof Module))
        {
            throw new \Exception(
                "Unable to determine the Honebee module for the current action's scope." . PHP_EOL .
                "Make sure that the Honeybee ModuleRoutingCallback is executed for the related route."
            );
        }

        return $module;
    }
}
