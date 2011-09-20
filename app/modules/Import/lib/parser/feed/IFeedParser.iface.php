<?php

/**
 * The IFeedParser interface defines the standard interface for parsing feeds inside this project.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Parser
 */
interface IFeedParser
{
    public function getTitle();
    public function getDescription();
    public function getLink();
    public function getTime();
    public function getCopyright();
    public function getItems();
}

?>