<?php

namespace Honeybee\Core\Import\Consumer;

/**
 * The ConsumerReport class is the base implementation of the IConsumerReport interface.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 */
class ConsumerReport implements IConsumerReport
{
    const SEVERITY_SUCCESS = 'success';

    const SEVERITY_ERROR = 'error';

    private $successCount = 0;

    private $errorCount = 0;

    private $data = array();

    public function addRecordSuccess(array $item, $msg = '')
    {
        $this->successCount++;

        $this->addReportData(array(
            'item_id' => uniqid(),
            'item' => $item,
            'message' => $msg
        ));
    }

    public function addRecordError(array $item, $msg = '')
    {
        $this->errorCount++;


        $reportData = array(
            'item_id' => uniqid(),
            'item' => $item,
            'message' => $msg
        );

        $this->addReportData($reportData, self::SEVERITY_ERROR);
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrors()
    {
        return isset($this->data[self::SEVERITY_ERROR])
            ? $this->data[self::SEVERITY_ERROR]
            : array();
    }

    public function hasErrors()
    {
        return 0 < $this->errorCount;
    }

    public function getMessages()
    {
        return isset($this->data[self::SEVERITY_ERROR])
            ? $this->data[self::SEVERITY_ERROR]
            : array();
    }

    protected function addReportData(array $data, $severity = self::SEVERITY_SUCCESS)
    {
        if (! isset($this->data[$severity]) || ! is_array($this->data[$severity]))
        {
            $this->data[$severity] = array();
        }

        $this->data[$severity][$data['item_id']] = $data;
    }
}
