<?php

/**
 * The MoviesWorkflowItem extends the WorkflowItem and serves as the aggregate root for all aggregated movie data objects.
 *
 * @version $Id: MoviesWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Movies
 * @subpackage Workflow/Item
 */
class MoviesWorkflowItem extends WorkflowItem
{
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function determineWorkflow()
    {
        return 'movies';
    }

    protected function getMasterRecordImplementor()
    {
        return "MoviesMasterRecord";
    }
}

?>
