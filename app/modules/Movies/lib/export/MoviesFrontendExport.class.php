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
        $theaterFactory = new FrontendTheaterFactory();
        $movieFactory = new FrontendMovieFactory();

        $movieDocument =  $movieFactory->createFromObject($movieItem);
        $this->frontendRepo->save($movieDocument);
        
        $theaterDocuments = array();
        foreach ($movieFactory->getRelatedTheaters($movieItem) as $theaterItem)
        {
            $theaterDocuments[] = $theaterFactory->createFromObject($theaterItem);
        }
        $this->frontendRepo->bulkSave($theaterDocuments);
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

?>
