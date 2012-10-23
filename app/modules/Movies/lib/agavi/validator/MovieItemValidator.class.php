<?php

class MovieItemValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());

        if (is_array($data))
        {
            $this->export($data, $this->getArgument());
            return TRUE;
        }
        return FALSE;
    }
}

?>