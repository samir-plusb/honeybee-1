<?php

/**
 * The IShofiDetailItem interface defines the structure off customer details that add in to the core data.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
interface IShofiDetailItem extends IDataObject
{
    public function getTeaser();

    public function setTeaser($teaser);

    public function getText();

    public function setText($text);

    public function getOpeningTimes();

    public function setOpeningTimes($openingTimes);

    public function getAttributes();

    public function setAttributes($attributes);

    public function getKeywords();

    public function setKeywords($keywords);

    public function getCategory();

    public function setCategory($category);

    public function getAdditionalCategories();

    public function setAdditionalCategories($additionalCategories);
}

?>
