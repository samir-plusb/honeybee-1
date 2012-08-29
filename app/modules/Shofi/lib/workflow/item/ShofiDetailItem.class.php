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

    protected $internalKeywords = array();

    protected $category;

    protected $additionalCategories = array();

    // structure: [{path:"string", url:"string", copyright:string}, {...}]
    protected $attachments = array();

    // string containing embed code
    protected $videoEmbedCode;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function softUpdate(array $data)
    {
        $updateData = array();
        foreach ($data as $propName => $currentValue)
        {
            if (empty($this->$propName))
            {
                $updateData[$propName] = $currentValue;
            }
        }
        $this->applyValues($updateData);
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
        $this->openingTimes = array_values(
            array_filter($openingTimes, function($item)
            {
                return !empty($item);
            })
        );
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : array();
        $this->attributes = array_values(
            array_filter($attributes, function($attribute)
            {
                return !empty($attribute);
            })
        );
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $keywords = is_array($keywords) ? $keywords : array();
        $this->keywords = array_values(
            array_filter($keywords, function($keyword)
            {
                return !empty($keyword);
            })
        );
    }

    public function getInternalKeywords()
    {
        return $this->internalKeywords;
    }

    public function setInternalKeywords($internalKeywords)
    {
        $internalKeywords = is_array($internalKeywords) ? $internalKeywords : array();
        $this->internalKeywords = array_values(
            array_filter($internalKeywords, function($internalKeyword)
            {
                return !empty($internalKeyword);
            })
        );
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
        $this->additionalCategories = array_values(
            array_filter($categories, function($additionalCategory)
            {
                return !empty($additionalCategory);
            })
        );
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

    public function getVideoEmbedCode()
    {
        return $this->videoEmbedCode;
    }

    public function setVideoEmbedCode($videoEmbedCode)
    {
        $this->videoEmbedCode = $videoEmbedCode;
    }
}
