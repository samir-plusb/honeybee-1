<?php

/**
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginSuccessView extends UserBaseView
{
    public function executeBinary(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $this->executeHtml($request_data);
    }

    /**
     * Execute any html related presentation logic and sets up our template attributes.
     *
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $routing = $this->getContext()->getRouting();
        $attributes = $this->getContainer()->getAttributes('org.agavi.controller.forwards.login', array());

        if (isset($attributes['requested_module']))
        {
            /*
            * Kein direkter Aufruf auf /user/login/ sondern ein Aufruf auf eine Action,
            * welche mit isSecure() = true markiert ist und der User war bis gerade eben
            * noch nicht eingeloggt.
            */
            $this->getResponse()->setRedirect($routing->gen(null));
        }
        else
        {
           $this->getResponse()->setRedirect($routing->getBaseHref());
        }
    }
}
