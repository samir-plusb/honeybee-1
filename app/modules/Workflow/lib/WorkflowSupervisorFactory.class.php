<?php

/**
 * The WorkflowSupervisorFactory is responseable for creating concrete workflow supervisor instances.
 *
 * @version         $Id: ShofiWorkflowSupervisor.class.php 1009 2012-03-02 19:01:55Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 */
class WorkflowSupervisorFactory
{
    public static function createByTypeKey($typeKey)
    {
        $supervisorImplementor = self::resolveClass($typeKey);
        $database = AgaviContext::getInstance()->getDatabaseManager()->getDatabase(
            $supervisorImplementor::getCouchDbDatabasename()
        );
        $supervisor = new $supervisorImplementor;
        $supervisor->initialize($database->getConnection());
        return $supervisor;
    }

    public static function resolveClass($key) 
    {
        $parsedKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        $supervisorImplementor = $parsedKey.'WorkflowSupervisor';
        if (! class_exists($supervisorImplementor))
        {
            throw new InvalidArgumentException(
                "Unable to instantiate a supervisor for the given key. Can not find class $supervisorImplementor"
            );
        }
        return $supervisorImplementor;
    }
}

?>
