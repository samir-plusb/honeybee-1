<?php

class SidebarTreeModuleValidator extends AgaviValidator
{
    protected function validate()
    {
        $moduleClassName = $this->getData($this->getArgument());

        if (is_string($moduleClassName) && class_exists($moduleClassName))
        {
            $this->export($this->getArgument(), $moduleClassName);
            return TRUE;
        }

        $this->throwError('type');
        return FALSE;
    }
}

