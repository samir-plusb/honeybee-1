<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\Document;

class ReferenceFilter extends BaseFilter
{
    public function execute(Document $document)
    {
        $output = array();

        $references = $this->getConfig()->get('references');

        foreach ($references as $reference => $fieldnames)
        {
            $output[$reference] = array();
            $referencedDocs = $document->getValue($reference);

            foreach ($referencedDocs as $refDocument)
            {
                $refData = array();

                foreach ($fieldnames as $fieldname)
                {
                    $refData[$fieldname] = $refDocument->getValue($fieldname);
                }

                $output[$reference][] = $refData;
            }
        }

        return $output;
    }
}
