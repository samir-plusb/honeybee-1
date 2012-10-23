<?php

class Shofi_LinkR4Action extends ShofiBaseAction
{
    CONST R4_LANG_DE = 61435;

    const COL_ID = 0;

    const COL_LANG = 1;

    const COL_IMPORT_ID = 2;

    const CSV_ID_OFFSET = 2;

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $shofiService = ShofiWorkflowService::getInstance();
        $filepath = $parameters->getParameter('csvfile');
        $typePrefix = $parameters->getParameter('prefix');
        if (! is_readable($filepath))
        {
            throw new InvalidArgumentException('The given csv file is not readable/does not exist: ' . $filepath);
        }

        $row = 1;
        if (($handle = fopen($filepath, "r")) !== FALSE) 
        {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
            {
                $r4Language = (int)$data[self::COL_LANG];
                if (self::R4_LANG_DE !== $r4Language)
                {
                    continue;
                }
                $r4Id = (int)$data[self::COL_ID];
                $btkParts = explode(':', $data[self::COL_IMPORT_ID]);
                $shofiImportId = $typePrefix . ':' . $btkParts[self::CSV_ID_OFFSET];
                $place = $shofiService->findItemByImportIdentifier($shofiImportId);
                if ($place)
                {
                    $place->setAttribute('r4id', $r4Id);
                    $shofiService->storeWorkflowItem($place);
                    echo 'Linked place with import-id: ' . $shofiImportId . 
                         ' and id: ' . $place->getIdentifier() . PHP_EOL;
                }
                else
                {
                    echo 'No place found for import-identifier: ' . $shofiImportId . PHP_EOL;
                }
                $row++;
            }
            fclose($handle);
        }

        return AgaviView::NONE;
    }

    public function handleError(AgaviRequestDataHolder $parameters)
    {
        var_dump($this->getContainer()->getValidationManager()->getErrorMessages());
        var_dump($parameters->getParameters());exit;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
