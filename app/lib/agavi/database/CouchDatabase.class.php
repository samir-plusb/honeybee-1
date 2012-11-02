<?php

/**
 * Provide couch database connection handle
 *
 * @author tay
 * @version $Id$
 * @since 10.10.2011
 * @package Project
 * @subpackage Agavi/Database
 */
class CouchDatabase extends AgaviDatabase
{
    /**
     * our database access handle instance
     *
     * @var ExtendedCouchDbClient
     */
    protected $connection;

    /**
     * uses parameter 'url' for connection the couch database
     *
     * @see AgaviDatabase::connect()
     */
    protected function connect()
    {
        $couchUri = $this->getParameter('url', ExtendedCouchDbClient::DEFAULT_URL);

        try
        {
            $this->connection = new ExtendedCouchDbClient(
                $couchUri,
                $this->getParameter('database', NULL),
                $this->getParameter('options', NULL)
            );
        }
        catch (CouchdbClientException $e)
        {
            throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }

        $this->login();
        $this->setDatabase();
    }

    /**
     * (non-PHPdoc)
     * @see AgaviDatabase::shutdown()
     */
    public function shutdown()
    {
        $this->connection = NULL;
    }

    /**
     * uses parameter 'database' for setup a default database and the parameter 'setup' to initialize the
     * freshly generated database
     *
     * @throws AgaviDatabaseException
     */
    protected function initDatabase()
    {
        if (! $this->hasParameter('database'))
        {
            throw new AgaviDatabaseException(
                "Database name required but missing in given configuration."
            );
        }

        $this->resource = $this->getParameter('database');
        if (FALSE === $this->connection->getDatabase($this->resource))
        {
            try
            {
                $this->connection->createDatabase($this->resource);
            }
            catch (CouchdbClientException $e)
            {
                throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }

            if ($this->hasParameter('setup'))
            {
                $this->setupDatabase();
            }
        }
    }

    /**
     * prepare database for use using the class defined in the parameter 'setup'
     *
     * The setup class must implement the interface ICouchDatabaseSetup
     *
     * @see ICouchDatabaseSetup
     * @throws AgaviDatabaseException
     */
    protected function setupDatabase()
    {
        $class = $this->getParameter('setup');

        if (! class_exists($class))
        {
            throw new AgaviDatabaseException('Setup class does not exists: '.$class);
        }
        $setup = new $class();
        if ($setup instanceof IDatabaseSetup)
        {
            $setup->setup($this);
        }
        else
        {
            throw new AgaviDatabaseException('Setup class does not implement ICouchDatabaseSetup: '.$class);
        }
    }

    /**
     * uses parameters 'user' and 'password' for user authentification
     *
     * @throws AgaviDatabaseException
     */
    protected function login()
    {
        if ($this->hasParameter('user') && $this->hasParameter('password'))
        {
            try
            {
                $status = $this->connection->login($this->getParameter('user'), $this->getParameter('password'));
                if (TRUE !== $status)
                {
                    throw new AgaviDatabaseException($status);
                }
            }
            catch (CouchdbClientException $e)
            {
                throw new AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }

    }
}
