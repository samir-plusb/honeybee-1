<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;

class ReferenceFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $filterOutput = array();

        $references = $this->getConfig()->get('references');

        foreach ($references as $reference => $fieldnames)
        {
            $filterOutput[$reference] = array();
            $referencedDocs = $document->getValue($reference);

            foreach ($referencedDocs as $refDocument)
            {
                $refData = array();

                foreach ($fieldnames as $fieldname)
                {
                    $refData[$fieldname] = $refDocument->getValue($fieldname);
                }

                $filterOutput[$reference][] = $refData;
            }
        }

        return $filterOutput;
    }
}
