<?php

class TicketFinderModel extends AgaviModel implements AgaviISingletonModel
{
    const DEFAULT_LIMIT = 50;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        self::parent($context, $parameters);
    }

    public function findTicketsByText($limit = self::DEFAULT_LIMIT, $offset = 0)
    {
        $couchClient = $this->getContext()->getDatabaseConnection('CouchWorkflow');
        $documents = $couchClient->getView(NULL, 'designWorkflow', 'ticketList',
            array(
                'limit'        => 500,
                'descending'   => TRUE,
                'include_docs' => TRUE
            )
        );
    }
}
