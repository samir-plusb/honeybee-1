<?php
/**
 * Provide couch database connection handle
 *
 * @author tay
 * @version $Id$
 * @since 10.10.2011
 *
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
            $this->connection = new ExtendedCouchDbClient($couchUri);
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
     * uses parameter 'database' for setup a default database
     *
     * @throws AgaviDatabaseException
     */
    protected function setDatabase()
    {
        $this->resource = $this->getParameter('database', NULL);
        if (! $this->hasParameter('database'))
        {
            return;
        }

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