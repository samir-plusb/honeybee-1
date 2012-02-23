<?php

class ImperiaImportTest extends IDataImportBaseTestCase
{
    protected $docCount;

    protected function getImportName()
    {
        return 'workflow';
    }

    // @codeCoverageIgnoreStart

    protected function getDataSourceNames()
    {
        return array('imperia');
    }

    protected function getDataSourceParameters($dataSourceName)
    {
        return array(
            ImperiaDataSourceConfig::CFG_DOCIDS => $this->fetchRandomImperiaDocIds()
        );
    }

    protected function fetchRandomImperiaDocIds()
    {
        $this->docCount = rand(2, 10);

        $imperiaUrl = AgaviConfig::get('import.imperia_cat_url');
        $params = array();
        parse_str(parse_url($imperiaUrl, PHP_URL_QUERY), $params);
        if (! isset($params['catid']))
        {
            throw new PHPUnit_Framework_Exception("Unable to parse imperia category id from the given url.");
        }

        $curlHandle = ProjectCurl::create();
        curl_setopt($curlHandle, CURLOPT_URL, $imperiaUrl);
        $resp = curl_exec($curlHandle);
        $err = curl_error($curlHandle);
        $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        if ($respCode !== 200 || $err)
        {
            throw new PHPUnit_Framework_Exception("Unable to download imperia test documents");
        }

        $matches = array();
        $pattern = sprintf('#<node_id>(%s\d+)</node_id>#is', $params['catid']);
        if (! preg_match_all($pattern, $resp, $matches, PREG_SET_ORDER))
        {
            throw new PHPUnit_Framework_Exception("No suitable test docs found inside imperia category.");
        }
        $docsRead = 0;
        foreach ($matches as $match)
        {
            if ($this->docCount === $docsRead)
            {
                break;
            }
            $docIds[] = $match[1];
            $docsRead++;
        }
        return $docIds;
    }

    // @codeCoverageIgnoreEnd
}

?>