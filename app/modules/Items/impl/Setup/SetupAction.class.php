<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Items
 */
class Items_SetupAction extends ItemsBaseAction
{
	/**
     * Execute the write logic for this action, hence process the given asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $moduleSetup = new ItemsModuleSetup();

        try
        {
            $moduleSetup->setup(TRUE);
        }
        catch (Exception $e)
        {
            throw $e;
            $this->setAttribute('errors', array($e->getMessage()));

            return 'Error';
        }

        return 'Success';
    }
}