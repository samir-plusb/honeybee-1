<?php

/**
 * The ShofiDetailItem holds the shofi location detail data.
 *
 * @version $Id: ShofiWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiDetailItem extends BaseDataObject implements IShofiDetailItem
{
    protected $teaser;

    protected $text;

    protected $openingTimes = array();

    protected $attributes = array();

    protected $keywords = array();

    protected $category;

    protected $additionalCategories = array();

    // structure: [{path:"string", url:"string", copyright:string}, {...}]
    protected $attachments = array();

    public static function fromArray(array $data = array())
    {
        return new self($data);
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

    public function getOpeningTimes()
    {
        return $this->openingTimes;
    }

    public function setOpeningTimes($openingTimes)
    {
        $openingTimes = is_array($openingTimes) ? $openingTimes : array();
        $this->openingTimes = array_filter($openingTimes, function($item)
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

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = empty($category) ? NULL : $category;
    }

    public function getAdditionalCategories()
    {
        return $this->additionalCategories;
    }

    public function setAdditionalCategories($additionalCategories)
    {
        $categories = is_array($additionalCategories) ? $additionalCategories : array();
        $this->additionalCategories = array_filter($categories, function($item)
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
