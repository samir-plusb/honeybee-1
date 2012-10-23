<?php

/**
 * The ShofiMasterRecord is a data object implementation of the IShofiEntity interface.
 * It holds the originally imported unmodified data as provided by the shofi import
 * and is used as the primary datasource for the shofi editing process.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiMasterRecord extends MasterRecord
{
    protected $coreItem = array();

    protected $detailItem = array();

    protected $salesItem = array();

    protected $categorySource;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getCoreItem()
    {
        return $this->coreItem;
    }

    public function setCoreItem(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->coreItem[$key] = $value;
        }
    }

    public function getDetailItem()
    {
        return $this->detailItem;
    }

    public function setDetailItem(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->detailItem[$key] = $value;
        }
    }

    public function getSalesItem()
    {
        return $this->salesItem;
    }

    public function setSalesItem(array $data)
    {
        foreach ($data as $key => $value)
        {
            if (! empty($value))
            {
                $this->salesItem[$key] = $value;
            }
        }
    }

    public function getCategorySource()
    {
        return $this->categorySource;
    }
}

?>
