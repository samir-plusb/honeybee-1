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
        $stdinFileData = NULL;

        if (strstr(AgaviConfig::get('core.environment'), 'testing'))
        {
            $stdinFileData = $this->getData('testmail');
        }
        else
        {
            $stdinFileData = $this->getData($this->getArgument());
        }

        if (! is_array($stdinFileData) || ! isset($stdinFileData['tmp_name']))
        {
            $this->throwError('invalid_data');

            return FALSE;
        }

        if (!is_readable($stdinFileData['tmp_name']))
        {
            $this->throwError('invalid_file');

            return FALSE;
        }

        $contents = file_get_contents($stdinFileData['tmp_name']);

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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    protected function checkAllArgumentsSet($throwError = TRUE) // @codingStandardsIgnoreEnd
    {
        return TRUE;
    }

    // ---------------------------------- </AgaviValidator OVERRIDES> ----------------------------
}

?>