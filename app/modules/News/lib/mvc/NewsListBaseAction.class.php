<?php

/**
 * The NewsListBaseAction serves as a base to all (item) actions that relate to list style data
 * and expose corresponding properties.
 * Actions that use this action as a base should xi:include the list's validation xml file.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class NewsListBaseAction extends NewsBaseAction
{
    /**
     * The alias of the default field used to sort our list data.
     */
    const DEFAULT_SORT_FIELD = 'timestamp';

    /**
     * The default direction used to sort our list data.
     */
    const DEFAULT_SORT_DIRECTION = NewsFinder::SORT_DESC;

    /**
     * Handles validation errors that occur for any our derivates.
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return string The name of the view to invoke.
     */
    public function handleError(AgaviRequestDataHolder $parameters)
    {
        parent::handleError($parameters);
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[implode(', ', array_values($errMsg['errors']))] = $errMsg['message'];
        }
        $this->setAttribute('error_messages', $errors);
        return 'Error';
    }

    /**
     * Helper method for setting up several common attributes
     * that are used amoung list related actions and essentailly by the self::loadItems method.
     *
     * @param AgaviRequestDataHolder $parameters
     */
    protected function setActionAttributes(AgaviRequestDataHolder $parameters)
    {
        $this->setAttribute('offset', $parameters->getParameter('offset', 0));
        $this->setAttribute('limit', $parameters->getParameter('limit', NewsFinder::DEFAULT_LIMIT));
        $this->setAttribute('sorting', array(
            'direction' => $parameters->getParameter('sorting[direction]', self::DEFAULT_SORT_DIRECTION),
            'field'     => $parameters->getParameter('sorting[field]', self::DEFAULT_SORT_FIELD)
        ));
        $this->setAttribute('finder', new NewsFinder(
            $this->getContext()->getDatabaseConnection('EsNews')
        ));

        $searchPhrase = $parameters->getParameter('search_phrase');
        if (! empty($searchPhrase))
        {
            $this->setAttribute('search_phrase', $searchPhrase);
        }

        // Valid for the prev and next item actions.
        $curOpenItem = $parameters->getParameter('cur_item');
        if ($curOpenItem)
        {
            $this->setAttribute('cur_item', $curOpenItem);
            $this->getAttribute('finder')->enableEditFilter($curOpenItem);
        }
    }

    /**
     * Helper metod for invoking the correct retrieval method on our NewsFinder,
     * depending whether a search phrase has been passed.
     * Depends on self::setActionAttributes() being called before.
     *
     * @return array
     * @see NewsFinder::search and NewsFinder::fetchAll for return value documentation.
     */
    protected function loadItems()
    {
        $itemFinder = $this->getAttribute('finder');
        $limit = $this->getAttribute('limit');
        $offset = $this->getAttribute('offset');
        $sorting = $this->getAttribute('sorting');

        if ($this->hasAttribute('search_phrase'))
        {
            return $itemFinder->search(
                strtolower($this->getAttribute('search_phrase')),
                $sorting['field'],
                $sorting['direction'],
                $offset,
                $limit
            );
        }

        return $itemFinder->fetchAll(
            $sorting['field'],
            $sorting['direction'],
            $offset,
            $limit
        );
    }
}

?>
