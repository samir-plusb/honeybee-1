<?php

/**
 * The ProjectAssetService is a concrete implementation of the IAssetService interface.
 * It exposes a coarse grained crud api for managing assets.
 *
 * @version         $Id: ProjectAssetService.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Service
 */
class ProjectAssetService implements IAssetService
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the index of the 'scheme' part of php's parse_url method's result.
     */
    const URI_PART_SCHEME = 'scheme';

    /**
     * Holds the index of the 'path' part of php's parse_url method's result.
     */
    const URI_PART_PATH = 'path';

    /**
     * Holds the string we use to identify file uri schemes.
     */
    const URI_SCHEME_FILE = 'file';

    /**
     * Holds the string we use to identify http uri schemes.
     */
    const URI_SCHEME_HTTP = 'http';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the client we use to talk to couchdb.
     *
     * @var         CouchDocumentStore
     */
    protected $assetDocStore;

    /**
     * Holds the client we use to talk to couchdb.
     *
     * @var         Elastica_Index
     */
    protected $assetDocIndex;

    /**
     * Holds the idsequence we use for new assets.
     *
     * @var         AssetIdSequence
     */
    protected $idSequence;

    /**
     * Holds the idsequence object that delivers new ids for asset creation.
     *
     * @var         ProjectAssetService
     */
    protected static $instance;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new ProjectAssetService instance.
     *
     * @param string $databaseName The name of an agavi database configuration to use.
     */
    public function __construct($writeConnection, $readConnection)
    {
        $context = AgaviContext::getInstance();
        $this->assetDocStore = new CouchDocumentStore(
            $context->getDatabaseConnection($writeConnection)
        );
        $this->assetDocIndex = $context->getDatabaseManager()->getDatabase($readConnection)->getResource();
        $this->idSequence = new AssetIdSequence($this->assetDocStore);
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns a static instance of this class.
     *
     * @return      ProjectAssetService
     */
    public static function getInstance($writeConnection = 'Assets.Write', $readConnection = 'Assets.Read')
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ProjectAssetService($writeConnection, $readConnection);
        }
        return self::$instance;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <IAssetService IMPL> -----------------------------------

    /**
     * Retrieves the corresponding IAssetInfo instance for a given $assetId.
     *
     * @return      IAssetInfo
     */
    public function get($assetId)
    {
        return $this->assetDocStore->fetchByIdentifier($assetId);
    }

    /**
     * Retrieves the corresponding list of IAssetInfo instances for a given list of $assetIds.
     *
     * @return      array
     */
    public function multiGet(array $assetIds)
    {
        $assets = array();
        if (empty($assetIds))
        {
            return $assets;
        }
        $query = Elastica_Query::create(NULL);
        $query->setFilter(
            new Elastica_Filter_Ids('asset', $assetIds)
        );
        $query->setSort(array(
            array('created.date' => IListState::SORT_ASC),
            array('_uid' => IListState::SORT_ASC)
        ));
        $query->setLimit(100);
        $result = $this->assetDocIndex->search($query);
        foreach ($result->getResults() as $result)
        {
            $asset = CouchDocumentStore::factory($result->getData());
            $assets[$asset->getIdentifier()] = $asset;
        }
        return $assets;
    }

    /**
     * Retrieves the corresponding IAssetInfo instance for a given $origin.
     *
     * @return      IAssetInfo
     */
    public function findByOrigin($origin)
    {
        $originFilter = new Elastica_Filter_Term();
        $originFilter->setTerm(array('origin.raw' => $origin));
        $query = Elastica_Query::create($originFilter);
        $resultSet = $this->assetDocIndex->getType('asset')->search($query);
        if (0 === $resultSet->count())
        {
            return NULL;
        }
        return CouchDocumentStore::factory($resultSet->current()->getData());
    }

    /**
     * Store the given file on the filesystem
     * and returns a IAssetInfo instance that reflects our new asset.
     *
     * @param       string $assetUri
     * @param       array $metaData
     * @param       boolean $moveOrigin
     *
     * @return      IAssetInfo
     */
    public function put($assetUri, array $metaData = array(), $moveOrigin = TRUE)
    {
        $assetFile = new AssetFile($this->idSequence->nextId());
        $assetFile->move($assetUri, $moveOrigin);
        $assetData = $this->buildAssetInfoData($assetFile, $assetUri, $metaData);
        $assetDocument = ProjectAssetInfo::fromArray($assetData);

        if (! $this->assetDocStore->save($assetDocument))
        {
            $assetFile->delete();
            throw new Exception("Unable to store asset meta data for asset: " . $assetFile->getId());
        }
        return $assetDocument;
    }

    /**
     * Deletes the IAssetInfo and it's corresponding binary for th given $assetId
     *
     * @return      IAssetInfo
     */
    public function delete($assetId)
    {
        $assetFile = new AssetFile($assetId);
        if ($this->assetDocStore->delete($assetId))
        {
            return $assetFile->delete();
        }
        return FALSE;
    }

    /**
     * Update the metadata of the asset with the given $assetId
     *
     * @param       int $assetId
     * @param       array $metaData
     *
     * @return      IAssetInfo
     */
    public function update(IAssetInfo $asset, array $metaData = array())
    {
        $asset->setMetaData(array_merge(
            $asset->getMetaData(),
            $metaData
        ));
        return $this->assetDocStore->save($asset);
    }

    // ---------------------------------- </IAssetService IMPL> ----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Return an array with 'ready to store' asset data.
     *
     * @param       string $assetFile
     * @param       string $assetUri
     * @param       array $metaData
     *
     * @return      array
     */
    protected function buildAssetInfoData(IAssetFile $assetFile, $assetUri, array $metaData = array())
    {
        $data = array('identifier' => $assetFile->getId());
        foreach (array("mimeType", "fullName") as $overrideProp)
        {
            if (isset($metaData[$overrideProp]))
            {
                $data[$overrideProp] = $metaData[$overrideProp];
                unset($metaData[$overrideProp]);
            }
        }
        $data['metaData'] = $metaData;
        return array_merge(
            $data,
            $this->extractFileInformation($assetFile, $assetUri)
        );
    }

    /**
     * Collect data about a given asset and return it in way,
     * that can be used to hydrate AIAssetInfo instances.
     *
     * @param       IAssetFile $file
     * @param       string $assetUri
     *
     * @return      array
     */
    protected function extractFileInformation(IAssetFile $file, $assetUri)
    {
        $originParts = parse_url($assetUri);
        $explodedOrigin = explode('/', $originParts['path']);
        $fullname = end($explodedOrigin);
        $explodedName = explode('.', $fullname);
        $extension = array_pop($explodedName);
        $name = implode('.', $explodedName);
        $data = array(
            'origin' => $assetUri,
            'fullName' => $fullname,
            'name' => $name,
            'extension' => $extension ? strtolower($extension) : NULL,
            'size' => filesize($file->getPath())
        );
// new finfo(FILEINFO_MIME, AgaviConfig::get('assets.mime_database'))
        if (($finfo = new finfo(FILEINFO_MIME)))
        {
            $data['mimeType'] = $finfo->file($file->getPath());
        }
        return $data;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
