<?php

class ShofiLastImportIdListRenderer extends ShofiTranslationListRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $lastImportSource = NULL;
        $lastImportSources = array();
		$primarySource = $data['data']['masterRecord']['source'];

        if (is_array($value))
        {
            foreach ($value as $lastImportId)
            {
                $parts = explode(':', $lastImportId);
                if (2 === count($parts) && $parts[0] != $primarySource)
                {
					
                    $lastImportSources[] = parent::renderValue($parts[0], $fieldname, $data);
                }
            }
        }

        return implode(', ', $lastImportSources);
    }
}
