<?php

/**
 * The IShofiSalesItem interface defines the structure exposed by the shofi sales context.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
interface IShofiSalesItem extends IDataObject
{
    public function getProduct();

    public function setProduct($product);

    public function getExpireDate();

    public function setExpireDate($expireDate);

    public function getTeaser();

    public function setTeaser($teaser);

    public function getText();

    public function setText($text);

    public function getAdditionalCategories();

    public function setAdditionalCategories($additionalCategories);

    public function getAttributes();

    public function setAttributes($attributes);

    public function getKeywords();

    public function setKeywords($keywords);
}

?>
