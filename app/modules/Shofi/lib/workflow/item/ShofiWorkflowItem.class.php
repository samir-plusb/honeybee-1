<?php

/**
 * The ShofiWorkflowItem extends the WorkflowItem and serves as the aggregate root for all shofi based data objects.
 *
 * @version $Id: ShofiWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiWorkflowItem extends WorkflowItem
{
    const ID_PREFIX = 'place-';

    /**
     * Holds our core data.
     *
     * @var IShofiCoreItem
     */
    protected $coreItem = NULL;

    /**
     * Holds a list with our IContentItems.
     *
     * @var IShofiDetailItem
     */
    protected $detailItem = NULL;

    /**
     * Holds a list with our IContentItems.
     *
     * @var IShofiSalesItem
     */
    protected $salesItem = NULL;

    /**
     * Holds a list with our various source documents.
     *
     * @var array
     */
    protected $sources = array();

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function determineWorkflow()
    {
        return 'shofi_places';
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function getDetailItem()
    {
        return $this->detailItem;
    }

    public function setDetailItem($item)
    {
        if (is_array($item))
        {
            $this->detailItem = ShofiDetailItem::fromArray($item);
        }
        elseif ($item instanceof IShofiDetailItem)
        {
            $this->detailItem = $item;
        }
        else if (NULL !== $item)
        {
            throw new Exception(
                "Invalid argument type passed to setDetailItem method. Only array and IShofiDetailItem are supported."
            );
        }
    }

    public function updateDetailItem(array $data)
    {
        $detailItem = $this->getDetailItem();

        if ($detailItem)
        {
            $detailItem->applyValues($data);
        }
    }

    public function getSalesItem()
    {
        return $this->salesItem;
    }

    public function setSalesItem($item)
    {
        if (is_array($item))
        {
            $this->salesItem = ShofiSalesItem::fromArray($item);
        }
        elseif ($item instanceof IShofiSalesItem)
        {
            $this->salesItem = $item;
        }
        else if (NULL !== $item)
        {
            throw new Exception(
                "Invalid argument type passed to setSalesItem method. Only array and IShofiSalesItem are supported."
            );
        }
    }

    public function updateSalesItem(array $data)
    {
        $salesItem = $this->getSalesItem();

        if ($salesItem)
        {
            $salesItem->applyValues($data);
        }
    }

    public function getCoreItem()
    {
        return $this->coreItem;
    }

    public function setCoreItem($item)
    {
        if (is_array($item))
        {
            $this->coreItem = ShofiCoreItem::fromArray($item);
        }
        elseif ($item instanceof IShofiCoreItem)
        {
            $this->coreItem = $item;
        }
        else if (NULL !== $item)
        {
            throw new Exception(
                "Invalid argument type passed to setCoreItem method. Only array and IShofiCoreItem are supported."
            );
        }
    }

    public function updateCoreItem(array $data)
    {
        $coreItem = $this->getCoreItem();

        if ($coreItem)
        {
            $coreItem->applyValues($data);
        }
    }

    protected function getMasterRecordImplementor()
    {
        return "ShofiMasterRecord";
    }

    public function getIdentifierPrefix()
    {
        return self::ID_PREFIX;
    }
}

?>
