<?php
/**
 * Provide elastic search database connection handle
 *
 * @package Database
 * @author tay
 * @version $Id$
 * @since 10.10.2011
 *
 */
class ElasticSearchDatabase extends AgaviDatabase
{
    /**
     * The elastic search index that is considered as our 'connection'
     * which stands for the resource this class works on.
     *
     * @var Elastica_Index
     */
    protected $connection;

    /**
     * The client used to talk to elastic search.
     *
     * @var Elastica_Client
     */
    protected $elasticaClient;

    protected function connect()
    {
        try
        {
            $this->elasticaClient = new Elastica_Client(
                array(
                    'host'      => $this->getParameter('host', 'localhost'),
                    'port'      => $this->getParameter('port', 9200),
                    'transport' => $this->getParameter('transport', 'Http')
                )
            );
        }
        catch (Exception $e)
        {
            throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }

        $this->connection = $this->elasticaClient->getIndex(
            $this->getParameter('index')
        );

        try
        {
            $this->connection->getStatus();
        }
        catch (Elastica_Exception_Response $e)
        {
            if (0 === strpos($e->getMessage(), 'IndexMissingException'))
            {
                $this->triggerSetupHook();
            }
            else
            {
                throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    public function shutdown()
    {
        $this->connection = NULL;
    }

    protected function triggerSetupHook()
    {
        if (! $this->hasParameter('setup_class'))
        {
            return;
        }

        $setupClass = $this->getParameter('setup_class');
        if (! class_exists($setupClass))
        {
            throw new AgaviDatabaseException("Setup class '$setupClass' can not be found.");
        }
        $setup = new $setupClass($this);
        if ($setup instanceof IDatabaseSetup)
        {
            $setup->setup();
        }
        else
        {
            throw new AgaviDatabaseException('Setup class does not implement IDatabaseSetup: '.$setupClass);
        }
    }

    protected function triggerTearDownHook()
    {
        if (! $this->hasParameter('setup_class'))
        {
            return;
        }

        $setupClass = $this->getParameter('setup_class');

        if (! class_exists($setupClass))
        {
            throw new AgaviDatabaseException("Setup class '$setupClass' can not be found.");
        }

        $setup = new $setupClass;
        $setup->tearDown();
    }
}

?>
