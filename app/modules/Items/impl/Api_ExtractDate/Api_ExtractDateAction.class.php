<?php

/**
 * The Items_Api_ExtractDateAction is repsonseable handling date extraction api requests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_Api_ExtractDateAction extends ItemsBaseAction
{

    /**
     * Execute the read logic for this action, hence extract the data.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $date_text = $parameters->getParameter('date_text');
        $this->setAttribute('date', $this->getDateFromString($date_text));

        return 'Success';
    }

    function getDateFromString($date_string)
    {
        // @codingStandardsIgnoreStart
        $months = 'Jan(uar)?|Feb(ruar)?|März|Mar|Apr(il)?|Mai|Jun(i)?|Jul(i|y)?|Aug(ust)?|Sep(t)?(ember)?|Okt(ober)?|Nov(ember)?|Dez(ember)?';
        // @codingStandardsIgnoreEnd
        $name2number = array(
            'jan' => 1,
            'januar' => 1,
            'feb' => 2,
            'februar' => 2,
            'märz' => 3,
            'mar' => 3,
            'april' => 4,
            'apr' => 4,
            'mai' => 5,
            'jun' => 6,
            'juni' => 6,
            'jul' => 7,
            'juli' => 7,
            'aug' => 8,
            'august' => 8,
            'sep' => 9,
            'sept' => 9,
            'september' => 9,
            'oktober' => 10,
            'okt' => 10,
            'nov' => 11,
            'november' => 11,
            'dez' => 12,
            'dezember' => 12
        );

        if (preg_match('/(\d{2}|\d{1})\.?\s*?(' . $months . '|\d{2}|\d{1}?)\.?\s*?(\d{2,4})?/i', $date_string, $match))
        {
            $day = $match[1];
            $month = strtolower($match[2]);
            $year = empty($match[14]) ? strftime("%Y", time()) : $match[14];
            if (array_key_exists($month, $name2number))
            {
                $month = $name2number[$month];
            }
            $time = mktime(12, 0, 0, $month, $day, $year);
            return strftime("%d.%m.%Y", $time);
        }
        else
        {
            return "";
        }
    }

}

?>