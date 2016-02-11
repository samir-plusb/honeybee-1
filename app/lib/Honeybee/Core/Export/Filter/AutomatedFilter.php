<?php

namespace Honeybee\Core\Export\Filter;

use Honeybee\Core\Dat0r\BaseDocument;
use Honeybee\Core\Import\Filter;
use Dat0r\Core\Document as Dat0r;

class AutomatedFilter extends BaseFilter
{
    public function execute(BaseDocument $document)
    {
        $filter_output = array();

        $property_map = $this->getConfig()->get('properties');
        $module = $document->getModule();
        $document_data = $document->toArray();

        $step = $document_data['workflowTicket'][0]['workflowStep'];
        $value = $step == "archived" ? true : false;
        Filter\RemapFilter::setArrayValue($filter_output, 'blocked', (bool)$value);

        error_log(json_encode($filter_output));
        return $filter_output;
    }

}
