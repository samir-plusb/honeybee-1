<?php

/**
 * The ShofiSalesItem holds data that reflect the sales requirements towards shofi location attributes.
 *
 * @version $Id: ShofiWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiSalesItem extends BaseDataObject implements IShofiSalesItem
{
    protected $product;

    protected $expireDate;

    protected $teaser;

    protected $text;

    protected $additionalCategories = array();

    protected $attributes = array();

    protected $keywords = array();

    // structure: [{path:"string", url:"string", copyright:string}, {...}]
    protected $attachments = array();

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
    }

    public function getTeaser()
    {
        return $this->teaser;
    }

    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getAdditionalCategories()
    {
        return $this->additionalCategories;
    }

    public function setAdditionalCategories($additionalCategories)
    {
        $additionalCategories = is_array($additionalCategories) ? $additionalCategories : array();
        $this->additionalCategories = array_filter($additionalCategories, function($item)
        {
            return !empty($item);
        });
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : array();
        $this->attributes = array_filter($attributes, function($item)
        {
            return !empty($item);
        });
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $keywords = is_array($keywords) ? $keywords : array();
        $this->keywords = array_filter($keywords, function($item)
        {
            return !empty($item);
        });
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments)
    {
        $assets = array();
        foreach ($attachments as $assetId)
        {
            if (is_numeric($assetId))
            {
                $assets[] = (int)$assetId;
            }
        }
        $this->attachments = $assets;
    }
}

?>
