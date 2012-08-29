<?php

class ShofiCategoryMatcher
{
    const SHOFI_CONFIG_DOCID = 'shofi.categories.matching_config';

    protected $couchClient;

    protected $matchingConfig;

    public function __construct(ExtendedCouchDbClient $couchClient)
    {
        $this->couchClient = $couchClient;
        $this->matchingConfig = $this->load();
    }

    public function hasMatchesFor($externalCategory)
    {
        $externalCategory = strtolower($externalCategory);
        $map = $this->getMatchMappings();
        return array_key_exists($externalCategory, $map);
    }

    public function getMatchesFor($externalCategory)
    {
        $externalCategory = strtolower($externalCategory);
        if (! $this->hasMatchesFor($externalCategory))
        {
            return NULL;
        }
        $map = $this->getMatchMappings();
        return $map[$externalCategory];
    }

    public function setMatchesFor($externalCategory, array $midasCategories = array())
    {
        $externalCategory = strtolower($externalCategory);
        if (empty($externalCategory))
        {
            return FALSE;
        }
        $this->matchingConfig['mappings'][$externalCategory] = $midasCategories;
        $mappings = &$this->matchingConfig['mappings']; 
        ksort($mappings);
        $this->couchClient->storeDoc(NULL, $this->matchingConfig);
        $this->matchingConfig = $this->load();
        
        return TRUE;
    }

    public function registerExternalCategory($externalCategory)
    {
        $externalCategory = strtolower($externalCategory);
        $this->matchingConfig['mappings'][$externalCategory] = array();
        $mappings = &$this->matchingConfig['mappings']; 
        ksort($mappings);
        $this->couchClient->storeDoc(NULL, $this->matchingConfig);
        $this->matchingConfig = $this->load();
    }

    public function getMatchMappings()
    {
        return $this->matchingConfig['mappings'];
    }

    protected function load()
    {
        try
        {
            return $this->couchClient->getDoc(NULL, self::SHOFI_CONFIG_DOCID);
        }
        catch(CouchdbClientException $e)
        {
            if (! preg_match('=404=is', $e->getMessage()))
            {
                throw $e;
            }
        }

        $this->couchClient->storeDoc(NULL, array(
            '_id' => self::SHOFI_CONFIG_DOCID,
            'mappings' => array()
        ));
        return $this->couchClient->getDoc(NULL, self::SHOFI_CONFIG_DOCID);
    }
}
