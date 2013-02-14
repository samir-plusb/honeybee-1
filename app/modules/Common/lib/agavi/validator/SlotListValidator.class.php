<?php

class TreeConfigValidator extends AgaviValidator
{
    protected function validate()
    {
        $slotList = $this->getData($this->getArgument());

        foreach($slotlist as $slot)
        {
            if (!$slot instanceof AgaviExecutionContainer)
            {
                $this->throwError('type');
                return FALSE;
            }
        }

        $this->export($this->getArgument(), $config);
        return TRUE;
    }
}

