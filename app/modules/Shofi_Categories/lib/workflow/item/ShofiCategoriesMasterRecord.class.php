<?php

/**
 * The ShofiCategoriesMasterRecord holds the main data of a ShofiCategory.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Categories
 * @subpackage Workflow/Item
 */
class ShofiCategoriesMasterRecord extends MasterRecord
{
    protected $name;

    protected $alias;

    protected $singular;

    protected $plural;

    protected $text;

    protected $keywords = array();

    protected $tags = array();

    protected $salesManager = array();

    protected $vertical = array();

    protected $attachments = array();

    protected $genderArticle;

    protected $isTopCategory = FALSE;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getSingular()
    {
        return $this->singular;
    }

    public function setSingular($singular)
    {
        $this->singular = $singular;
    }

    public function getPlural()
    {
        return $this->plural;
    }

    public function setPlural($plural)
    {
        $this->plural = $plural;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords)
    {
        $this->keywords = array_values(
            array_filter($keywords, function($keyword)
            {
                return !empty($keyword);
            })
        );
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = array_values(
            array_filter($tags, function($item)
            {
                return !empty($item);
            })
        );
    }

    public function getSalesManager()
    {
        return $this->salesManager;
    }

    public function setSalesManager(array $salesManager)
    {
        $this->salesManager = $salesManager;
    }

    public function getVertical()
    {
        return $this->vertical;
    }

    public function setVertical(array $vertical)
    {
        $this->vertical = $vertical;
    }

    public function getGenderArticle()
    {
        return $this->genderArticle;
    }

    public function setGenderArticle($genderArticle)
    {
        $this->genderArticle = $genderArticle;
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

    public function getIsTopCategory()
    {
        return $this->isTopCategory;
    }

    public function setIsTopCategory($isTopCategory)
    {
        $this->isTopCategory = (bool)$isTopCategory;
    }
}

?>
