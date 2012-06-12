<?php

/**
 *
 * @version         $Id: ShofiVerticalItemValidator.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Verticals
 * @subpackage      Agavi/Validator
 */
class ShofiVerticalItemValidator extends AgaviValidator
{
    protected function validate()
    {
        $data = $this->getData($this->getArgument());

        if (is_array($data))
        {
            $this->export($data, $this->getArgument());
            return TRUE;
        }
        return FALSE;
    }
}

?>