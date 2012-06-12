<?php

class PrototypeImportValidator extends AgaviValidator
{
	protected function validate()
	{
		$data = $this->getData($this->getArgument());

		foreach ($data as $place)
        {
            if (! isset($place['id']))
            {
                $this->throw('id_missing');
                return FALSE;
            }
        }

        $this->export($data, $this->getArgument());
		return TRUE;
	}
}

?>
