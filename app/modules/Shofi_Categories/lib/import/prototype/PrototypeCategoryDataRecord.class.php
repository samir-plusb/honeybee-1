<?php

/**
 * The PrototypeCategoryDataRecord class is a concrete implementation of the ShofiDataRecord base class.
 * It provides handling for mapping data coming from the prototype into the local shofi record format.
 *
 * @version         $Id: PrototypeCategoryDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Shofi_Categories
 * @subpackage      Import/Prototype
 */
class PrototypeCategoryDataRecord extends ShofiCategoriesDataRecord
{
    /**
     * Map the incoming prototype style (array)data into the local shofi format.
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        return array(
            self::PROP_IDENT => $data['id'],
            self::PROP_NAME => $data['name'],
            self::PROP_ALIAS => isset($data['alias']) ? $data['alias'] : NULL,
            self::PROP_SINGULAR => isset($data['singular']) ? $data['singular'] : NULL,
            self::PROP_PLURAL => isset($data['plural']) ? $data['plural'] : NULL,
            self::PROP_TEXT => isset($data['text']) ? $data['text'] : NULL,
            self::PROP_KEYWORDS => isset($data['keywords']) ? $data['keywords'] : array(),
            self::PROP_TAGS => isset($data['tags']) ? $data['tags'] : array(),
            self::PROP_GENDER_ARTICLE => isset($data['article']) ? $data['article'] : NULL,
            self::PROP_SALES_MANAGER => (isset($data['responsible_sales_manager'])
                && is_array($data['responsible_sales_manager']))
                ? $data['responsible_sales_manager']
                : array(),
            self::PROP_SOURCE => 'prototype.category',
            self::PROP_VERTICAL => array(
                'id' => $data['vertical_id'],
                'name' => $data['vertical_name']
            )
        );
    }
}

?>
