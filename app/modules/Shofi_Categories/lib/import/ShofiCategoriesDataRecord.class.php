<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi_Categories
 * @subpackage      Import
 */
abstract class ShofiCategoriesDataRecord extends BaseDataRecord
{
    const PROP_NAME = 'name';

    const PROP_SINGULAR = 'singular';

    const PROP_PLURAL = 'plural';

    const PROP_TEXT = 'text';

    const PROP_KEYWORDS = 'keywords';

    const PROP_TAGS = 'tags';

    const PROP_SALES_MANAGER = 'salesManager';

    const PROP_VERTICAL = 'vertical';

    const PROP_GENDER_ARTICLE = 'genderArticle';

    protected $name;

    protected $alias;

    protected $singular;

    protected $plural;

    protected $text;

    protected $keywords = array();

    protected $tags = array();

    protected $salesManager;

    protected $vertical;

    protected $genderArticle;

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_NAME,
                self::PROP_SINGULAR,
                self::PROP_PLURAL,
                self::PROP_TEXT,
                self::PROP_KEYWORDS,
                self::PROP_TAGS,
                self::PROP_SALES_MANAGER,
                self::PROP_VERTICAL,
                self::PROP_GENDER_ARTICLE
            )
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSingular()
    {
        return $this->singular;
    }

    public function getPlural()
    {
        return $this->plural;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getSalesManager()
    {
        return $this->salesManager;
    }

    public function getVertical()
    {
        return $this->vertical;
    }

    public function getGenderArticle()
    {
        return $this->genderArticle;
    }
}

?>
