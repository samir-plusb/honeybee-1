<?php

class BaseExportDocumentFactory
{
    public function buildDocumentIdentifier(IDataObject $dataObject)
    {
        return $dataObject->getIdentifier();
    }

    public function buildSlug(IDataObject $dataObject)
    {
        $pattern = $this->getSlugPattern($dataObject);
        $propNames = array();
        $matches = array();
        preg_match_all('/<([\w\.]+)>/is', $pattern, $matches);
        if (! isset($matches[1]))
        {
            throw new Exception(
                "Unable to build slug, due to a non parseable slug pattern: " . $pattern
            );
        }
        $slugVals = array();
        $slugTokens = array();
        foreach ($matches[1] as $fieldname)
        {
            $curScope = $dataObject;
            $propValue = NULL;
            $pathParts = explode('.', $fieldname);
            for ($i = 0; $i < count($pathParts); $i++)
            {
                $part = $pathParts[$i];
                $getter = 'get' . ucfirst($part);
                if (is_callable(array($curScope, $getter)))
                {
                    $propValue = $curScope->$getter();
                }
                else
                {
                    throw new Exception(
                        "Unable to build slug due to an invalid slug fragment." . PHP_EOL . 
                        "Pattern: " . $pattern . PHP_EOL .
                        "Part: " . $part . PHP_EOL . 
                        "Notice: All slug fragments must be exposed by public getters."
                    );
                }
                if ($i < count($pathParts) - 1)
                {
                    $curScope = $propValue;
                }
            }
            if (! empty($propValue))
            {
                $slugifyHook = 'slugify'.str_replace(' ', '', ucwords(implode(' ', $pathParts)));
                if (is_callable(array($this, $slugifyHook)))
                {
                    $slugVals[] = $this->$slugifyHook($dataObject);
                }
                else
                {
                    $slugVals[] = $this->slugify($propValue);
                }
                $slugTokens[] = sprintf('<%s>', $fieldname);
            }
        }
        return str_replace($slugTokens, $slugVals, $pattern);
    }

    public function slugify($text)
    { 
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        {
            return 'n-a';
        }
        return $text;
    }

    protected function buildAssetIds(array $assetIds)
    {
        $assetsData = array();
        foreach ($assetIds as $assetId)
        {
            if (($data = $this->buildAssetId($assetId)))
            {
                $assetsData[] = $data;
            }
        }
        return $assetsData;
    }

    protected function buildAssetId($assetId)
    {
        return 'asset-' . $assetId;
    }
}
