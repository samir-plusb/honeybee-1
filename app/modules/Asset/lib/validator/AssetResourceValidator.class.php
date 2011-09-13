<?php

/**
 * The AssetValidator class provides validation of asset resources given from various inputs
 * and always exports a valid asset uri that can be used with the ProjectAssetService.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Validation
 */
class AssetResourceValidator extends AgaviFileValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of our 'export_asset' parameter.
     * This parameter defines the parameter name inside our request-data
     * at which our validated IAssetInfo object will be exported to.
     */
    const PARAM_EXPORT = 'export';
    
    /**
     * Holds the default value that is used when no custom self::PARAM_EXPORT_ASSET has been defined.
     */
    const DEFAULT_EXPORT = 'asset_uri';
    
    /**
     * Holds the name of the error thrown for invalid asset resources.
     */
    const ERR_INVALID_ASSET = 'invalid_asset';
    
    /**
     * Holds the name of the argument that specifies where to look for our asset resource.
     */
    const ARG_ASSET = 'asset';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <AgaviFileValidator OVERRIDES> -------------------------
    
    /**
     * Validates that given asset resource is either a valid uri or uploaded file.
     * 
     * @return      boolean
     * 
     * @see         AgaviFileValidator::validate()
     */
    protected function validate()
    {
        $fileUri = NULL;
        
        if ($this->getParameter('source') === AgaviWebRequestDataHolder::SOURCE_FILES)
        {
            if (!parent::validate() || !($fileUri = $this->moveUploadedFile()))
            {
                return FALSE;
            }
        }
        elseif (is_string($data = $this->getData($this->getArgument())))
        {
            $fileUri = $data;
        }
        else
        {
            $this->throwError('invalid_type');
            
            return FALSE;
        }
        
        $fileUri = $this->fixUri($fileUri);
        
        if (!$this->assetExists($fileUri))
        {
            $this->throwError('non_existant');
            
            return FALSE;
        }
        
        $this->export(
            $fileUri,
            $this->getParameter(self::PARAM_EXPORT, self::DEFAULT_EXPORT),
            AgaviRequestDataHolder::SOURCE_PARAMETERS
        );
        
        return TRUE;
    }
    
    /**
     * Export a given value to our request data.
     * The method was overriden to add support for defining the request data source,
     * that the value is exported to.
     * 
     * @param       mixed $value
     * @param       string $name
     * @param       string $paramType
     */
    protected function export($value, $name = null, $paramType = null)
	{
		if($name === null) 
        {
			$name = $this->getParameter('export');
		}

		if(!is_string($name) || $name === '') 
        {
			return;
		}
        
        if($paramType === null) 
        {
			$paramType = $this->getParameter('source');
		}

		$array =& $this->validationParameters->getAll($paramType);
		$currentParts = $this->curBase->getParts();
		
		if(count($currentParts) > 0 && strpos($name, '%') !== false) 
        {
			// this is a validator which actually has a base (<arguments base="xx">) set
			// and the export name contains sprintf syntax
			$name = vsprintf($name, $currentParts);
		}
		// CAUTION
		// we had a feature here during development that would allow [] at the end to append values to an array
		// that would, however, mean that we have to cast the value to an array, and, either way, a user would be able to manipulate the keys
		// example: we export to foo[], and the user supplies ?foo[28] in the URL. that means our export will be in foo[29]. foo[28] will be removed by the validation, but the keys are still potentially harmful
		// that's why we decided to remove this again
		$cp = new AgaviVirtualArrayPath($name);
		$cp->setValue($array, $value);
        
		if($this->parentContainer !== null) 
        {
			// make sure the parameter doesn't get removed by the validation manager
			if(is_array($value)) 
            {
				// for arrays all child elements need to be marked as not processed
				foreach(AgaviArrayPathDefinition::getFlatKeyNames($value) as $keyName) 
                {
					$this->parentContainer->addArgumentResult(
                        new AgaviValidationArgument(
                            $cp->pushRetNew($keyName)->__toString(), 
                            $paramType
                        ), 
                        AgaviValidator::SUCCESS, 
                        $this
                    );
				}
			}
            
			$this->parentContainer->addArgumentResult(
                new AgaviValidationArgument(
                    $cp->__toString(), 
                    $paramType
                ), 
                AgaviValidator::SUCCESS, 
                $this
            );
		}
	}
    
    // ---------------------------------- <AgaviFileValidator OVERRIDES> -------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Returns a valid uri, fixing a potentially
     * missing scheme for file uris.
     * 
     * @param       string $assetUri
     * 
     * @return      string 
     */
    protected function fixUri($assetUri)
    {
        $fixedUri = $assetUri;
        
        $pattern = '~^(\w+://).*$~is';
        
        if(!preg_match($pattern, $assetUri))
        {
            if ($assetUri{0} !== DIRECTORY_SEPARATOR)
            {
                $fixedUri = getcwd() . DIRECTORY_SEPARATOR . $assetUri;
            }
            
            $fixedUri = 'file://' . $fixedUri;
        }
        
        return $fixedUri;
    }
    
    /**
     * Check if a given asset uri points to an available asset.
     * 
     * @param       string $assetUri
     * 
     * @return      boolean
     */
    protected function assetExists($assetUri)
    {
        $uriParts = parse_url($assetUri);
        
        if ('http' === $uriParts['scheme'])
        {
            $curlHandle = ProjectCurl::create();
            curl_setopt($curlHandle, CURLOPT_HEADER, 1);
            curl_setopt($curlHandle, CURLOPT_NOBODY, 1);
            curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_URL, $assetUri);
            
            curl_exec($curlHandle);
            $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            
            if (200 > $respCode || 300 <= $respCode)
            {
                return FALSE;
            }
            
            return TRUE;
        }
        elseif ('file' === $uriParts['scheme'])
        {
            return file_exists($uriParts['path']);
        }
        
        return FALSE;
    }
    
    /**
     * Move a given uploaded file to a temp path.
     * 
     * @return      string Return FALSE if something goes wrong.
     */
    protected function moveUploadedFile()
    {
        $tmpDir = $this->getAssetTmpDir();
        $uploadedFile = $this->getData($this->getArgument());
        $tmpPath = $tmpDir . $uploadedFile->getName();

        if (!$uploadedFile->move($tmpPath))
        {
            $this->throwError('move_tmpfile');

            return FALSE;
        }
        
        return $tmpPath;
    }
    
    /**
     *  Return a path pointing to our asset tmp dir.
     * 
     * @return      string 
     */
    protected function getAssetTmpDir()
    {
        $baseDir = AgaviConfig::get('assets.base_dir');
        
        $tmpDir = realpath($baseDir) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($tmpDir))
        {
            if (!mkdir($tmpDir))
            {
                return false;
            }
        }
        
        return $tmpDir;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>