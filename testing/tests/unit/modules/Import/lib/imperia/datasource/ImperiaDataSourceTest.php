<?php

class ImperiaDataSourceTest extends DataSourceBaseTestCase
{
    const CFG_CONFIG_FIXTURE = 'import/config/config.datasource.php';

    protected $docCount;

    protected function getDataSourceClass()
    {
        return 'ImperiaDataSource';
    }

    protected function getDataSourceName()
    {
        return 'imperia';
    }

    protected function getExpectedLoopCount()
    {
        return $this->docCount;
    }

    protected function getExpectedRecordType()
    {
        return 'PoliceReportDataRecord';
    }

    protected function getDataSourceParameters()
    {
        return array(
            ImperiaDataSourceConfig::CFG_DOCIDS => $this->fetchRandomImperiaDocIds()
        );
    }

    protected function getDataSourceDescription()
    {
        return 'Provides imperia export xml data.';
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
}

?>