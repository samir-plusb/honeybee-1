<?php

use \Honeybee\Agavi\Action\BaseAction;

/**
 * The base action from which all Import module actions inherit.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Agavi/Action
 */
class ImportBaseAction extends BaseAction
{
    /**
     * Handle our validation(write) errors.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string Name of the error view to use.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return 'Error';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
