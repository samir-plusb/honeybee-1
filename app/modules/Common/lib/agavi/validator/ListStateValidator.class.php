<?php

class ListStateValidator extends AgaviValidator
{
    protected function validate()
    {
        $state = $this->getData($this->getArgument());

        if (is_array($state))
        {
            $state = ListState::fromArray($state);
        }
        elseif (! $state instanceof IListState)
        {
            $this->throwError('type');
            return FALSE;
        }

        if ($this->validateListStateInstance($state))
        {
            $this->export($this->getArgument(), $state);
            return TRUE;
        }
        return FALSE;
    }

    protected function validateListStateInstance(IListState $listState)
    {
        // @todo validate all important parameters
        return TRUE;
    }
}

?>
