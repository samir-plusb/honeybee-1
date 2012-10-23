<?php

class MoviesFrontendExport
{
    protected $frontendRepo;

    public function __construct()
    {
        $connections = AgaviConfig::get('movies.connections');
        $this->frontendRepo = new CouchDocumentStore(
            AgaviContext::getInstance()->getDatabaseConnection($connections['frontend'])
        );
    }

    public function exportMovie(MoviesWorkflowItem $movieItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));
        $theaterFactory = new FrontendTheaterFactory();
        $movieFactory = new FrontendMovieFactory();
        
        $theaterDocuments = array();
        foreach ($finder->findRelatedTheaters($movieItem) as $theaterItem)
        {
            $assetFactory = new FrontendTheaterAssetFactory();
            $assets = $assetFactory->createAssets($theaterItem);
            $this->frontendRepo->bulkSave($assets);
            $theaterDocuments[] = $theaterFactory->createFromObject($theaterItem);
        }
        $this->frontendRepo->bulkSave($theaterDocuments);

        $assetFactory = new FrontendMovieAssetFactory();
        $assets = $assetFactory->createAssets($movieItem);
        $this->frontendRepo->bulkSave($assets);

        $movieDocument =  $movieFactory->createFromObject($movieItem);
        $this->frontendRepo->save($movieDocument);
    }

    public function deleteMovie(MoviesWorkflowItem $movieItem)
    {
        $finder = MoviesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('movies.list_config')
        ));

        $exportId = $movieItem->getExportId();
        $feMovie = $this->frontendRepo->fetchByIdentifier('movie-' . $exportId);
        
        if ($feMovie)
        {
            $assetIds = array();
            $media = $feMovie->getMedia();

            if (isset($media['images']))
            {
                $assetIds = array_values($media['images']);
            }
            
            if (isset($media['galleries']))
            {
                foreach ($media['galleries'] as $imageIds)
                {
                    $assetIds = array_merge($assetIds, $imageIds);
                }
            }

            foreach ($assetIds as $assetId)
            {
                $asset = $this->frontendRepo->fetchByIdentifier($assetId);
                if ($asset)
                {
                    $this->frontendRepo->delete($asset);
                }
            }
            $this->frontendRepo->delete($feMovie);
            var_dump("YAY I DELETED A MOVIE!");
        }
    }

    public function exportTheater(ShofiWorkflowItem $theaterItem)
    {
        $theaterFactory = new FrontendTheaterFactory();
        $movieFactory = new FrontendMovieFactory();

        $theaterDocument =  $theaterFactory->createFromObject($theaterItem);
        $this->frontendRepo->save($theaterDocument);

        $movieDocuments = array();
        foreach ($theaterFactory->getRelatedMovies($theaterItem) as $movieItem)
        {
            $movieDocuments[] = $movieFactory->createFromObject($movieItem);
        }

        $this->frontendRepo->bulkSave($movieDocuments);
    }
}
