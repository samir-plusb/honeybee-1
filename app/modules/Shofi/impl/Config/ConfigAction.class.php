<?php

/**
 * The Shofi_ConfigAction class is responseable for handling the config settings that are mutable to users.
 *
 * @version         $Id: Import_ImperiaAction.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_ConfigAction extends ShofiBaseAction
{
    /**
     * Execute the write logic for this action, hence run the import.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        $matcher = new ShofiCategoryMatcher(
            $this->getContext()->getDatabaseConnection('Shofi.Write')
        );

        $this->setAttribute('matcher', $matcher);

        return 'Success';
    }

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $mappings = $parameters->getParameter('mappings', array());     
        $categoryString = $parameters->getParameter('category');     

        $categoryMatcher = new ShofiCategoryMatcher(
            $this->getContext()->getDatabaseConnection('Shofi.Write')
        );

        $categoryMatcher->setMatchesFor($categoryString, $mappings);

        return 'Success';
    }

    /**
     * Handles validation errors that occur for any our derivates.
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return string The name of the view to invoke.
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters)
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[implode(', ', array_values($errMsg['errors']))] = $errMsg['message'];
        }
        $this->setAttribute('error_messages', $errors);
        
        return 'Error';
    }

}
