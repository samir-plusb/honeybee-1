<?php

class ShofiLastImportIdListRenderer extends ShofiTranslationListRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $lastImportSource = NULL;
        $lastImportId = FALSE;
        if (is_array($value))
        {
            $lastImportId = array_pop($value);
        }

        if ($lastImportId)
        {
            $parts = explode(':', $lastImportId);
            if (2 === count($parts))
            {
                $lastImportSource = $parts[0];
            }
        }

        return parent::renderValue($lastImportSource, $fieldname, $data);
    }
}
