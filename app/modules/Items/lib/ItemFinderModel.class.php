<?php

class ItemFinderModel extends AgaviModel implements AgaviISingletonModel
{
    const DEFAULT_LIMIT = 50;

    /**
     *
     * @var Elastica_Client
     */
    protected $elasticClient;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->elasticClient = new Elastica_Client(array(
            'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
            'port'      => AgaviConfig::get('elasticsearch.port', 9200),
            'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
        ));
    }

    public function findItemsByText($textPhrase, $limit = self::DEFAULT_LIMIT, $offset = 0)
    {
        $textQuery = new Elastica_Query_Text();
		$textQuery->setFieldQuery('_all', $textPhrase);

        $query = new Elastica_Query($textQuery);
        $query->setLimit($limit);
        $query->setFrom($offset);

        $index = $this->elasticClient->getIndex('midas');
        $result = $index->search($query);
        $items = array();
        /* @var $items Elastica_Result */
        foreach($result->getResults() as $result)
        {
            $items[] = new WorkflowItem($result->getData());
        }
        return $items;
    }

    public function findItemsById(array $itemIds)
    {
        $rawQuery = array('ids' => $itemIds);
        $queryString = json_encode($rawQuery);

        $uri = sprintf(
            '%s://%s:%d/%s/%s/_mget',
            strtolower(AgaviConfig::get('elasticsearch.transport', 'Http')),
            AgaviConfig::get('elasticsearch.host', 'localhost'),
            AgaviConfig::get('elasticsearch.port', 9200),
            'midas',
            'ticket'
        );

        $curl = ProjectCurl::create();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET') ;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($curl, CURLOPT_PROXY, '');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Length: ' . mb_strlen($queryString),
            'Accept: application/json',
            'Content-Type: application/json; charset=utf-8',
        ));

        $resp = curl_exec($curl);

        if (curl_errno($curl))
        {
            var_dump(curl_getinfo($curl));
        }
        var_dump($resp);exit;
    }
}
