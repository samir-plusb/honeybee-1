<?php

namespace Honeybee\Agavi\Validator;

class TreeJsonValidator extends \AgaviValidator
{
    protected function validate()
    {
        $dataJson = $this->getData($this->getArgument());

        $tree = json_decode($dataJson, true);

        if ($tree !== NULL && !json_last_error())
        {
            if ($this->hasParameter('export'))
            {
                $this->export($tree, $this->getParameter('export'));
            }
            else
            {
                $this->export($tree, $this->getArgument());
            }

            return TRUE;
        }

        $this->throwError('format');
        return FALSE;
    }  
}
