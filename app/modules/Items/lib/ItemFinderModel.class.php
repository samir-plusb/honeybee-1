<?php

class ItemFinderModel extends AgaviModel implements AgaviISingletonModel
{
    const DEFAULT_LIMIT = 50;

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);
    }

    public function findByIds(array $itemIds, $offset = 0, $limit = self::DEFAULT_LIMIT)
    {
        $items = array();
        if (empty($itemIds))
        {
            return $items;
        }
        $rawQuery = array('ids' => $itemIds);
        $queryString = json_encode($rawQuery);
        $uri = sprintf(
            '%s://%s:%d/%s/%s/_mget',
            strtolower(AgaviConfig::get('elasticsearch.transport', 'Http')),
            AgaviConfig::get('elasticsearch.host', 'localhost'),
            AgaviConfig::get('elasticsearch.port', 9200),
            'midas',
            'item'
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
        if (curl_error($curl))
        {
            return NULL;
        }
        $data = json_decode($resp, TRUE);
        foreach ($data['docs'] as $doc)
        {
            if (is_array($doc['_source']))
            {
                $items[] = new WorkflowItem($doc['_source']);
            }
            else
            {
                // log corrupt data, this should not happen.
            }
        }
        return $items;
    }
}
