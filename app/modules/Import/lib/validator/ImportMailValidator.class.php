<?php

class ImportMailValidator extends AgaviValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the parameter than can be used to set the name of the request-data field,
     * that we shall export the vaidated data to.
     */
    const PARAM_EXPORT = 'export';

    /**
     * Holds the name of the default request-data field used to export data to,
     * when no self::PARAM_EXPORT has been provided.
     */
    const DEFAULT_PARAM_EXPORT = 'rawmail';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <AgaviValidator IMPL> ----------------------------------

    /**
     * Validate that the given data (rawmail mime string coming from stdin), is not empty.
     * @todo Is there more we can do?
     *
     * @return      boolean
     */
    protected function validate()
    {
        $stdinFilePath = $this->getData($this->getArgument());

        $uploadedFile = new AgaviUploadedFile($stdinFilePath);

        $contents = $uploadedFile->getContents();

        if (empty($contents))
        {
            $this->throwError();

            return FALSE;
        }

        $this->export(
            $contents, $this->getParameter(
                self::PARAM_EXPORT, self::DEFAULT_PARAM_EXPORT
            ), AgaviRequestDataHolder::SOURCE_PARAMETERS
        );

        return TRUE;
    }

    // ---------------------------------- </AgaviValidator IMPL> ---------------------------------


    // ---------------------------------- <AgaviValidator OVERRIDES> -----------------------------

    /**
     * Tells if all our arguments are availble inside the raw request data.
     *
     * @param       bollean $throwError
     *
     * @return      boolean
     */
    protected function checkAllArgumentsSet($throwError = TRUE)
    {
        return TRUE;
    }

    /**
     * Export a given value to our request data.
     * The method was overriden to add support for defining the request data source,
     * that the value is exported to.
     *
     * @internal    As we don't want further modifications of this method,
     *              to keep the compatibilty impact as small as possible,
     *              we'll ignore the pmd warnings for thsi method.
     *
     * @param       mixed $value
     * @param       string $name
     * @param       string $paramType
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function export($value, $name = NULL, $paramType = NULL)
    {
        if ($name === NULL)
        {
            $name = $this->getParameter('export');
        }

        if (!is_string($name) || $name === '')
        {
            return;
        }

        if ($paramType === NULL)
        {
            $paramType = $this->getParameter('source');
        }

        $array = & $this->validationParameters->getAll($paramType);
        $currentParts = $this->curBase->getParts();

        if (count($currentParts) > 0 && strpos($name, '%') !== FALSE)
        {
            // this is a validator which actually has a base (<arguments base="xx">) set
            // and the export name contains sprintf syntax
            $name = vsprintf($name, $currentParts);
        }
        // CAUTION
        // we had a feature here during development that would allow [] at the end to append values to an array
        // that would, however, mean that we have to cast the value to an array,
        // and, either way, a user would be able to manipulate the keys
        // example: we export to foo[], and the user supplies ?foo[28] in the URL.
        // That means our export will be in foo[29]. foo[28] will be removed by the validation,
        // but the keys are still potentially harmful, that's why we decided to remove this again.
        $arrayPath = new AgaviVirtualArrayPath($name);
        $arrayPath->setValue($array, $value);

        if ($this->parentContainer !== NULL)
        {
            // make sure the parameter doesn't get removed by the validation manager
            if (is_array($value))
            {
                // for arrays all child elements need to be marked as not processed
                foreach (AgaviArrayPathDefinition::getFlatKeyNames($value) as $keyName)
                {
                    $this->parentContainer->addArgumentResult(
                        new AgaviValidationArgument(
                            $arrayPath->pushRetNew($keyName)->__toString(),
                            $paramType
                        ), AgaviValidator::SUCCESS, $this
                    );
                }
            }

            $this->parentContainer->addArgumentResult(
                new AgaviValidationArgument(
                    $arrayPath->__toString(),
                    $paramType
                ), AgaviValidator::SUCCESS, $this
            );
        }
    }
    
    // ---------------------------------- </AgaviValidator OVERRIDES> ----------------------------
}

?>