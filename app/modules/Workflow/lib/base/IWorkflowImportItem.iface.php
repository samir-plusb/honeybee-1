<?php
/**
 * This interface defines the requirements of an import item in the workflow context
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
interface IWorkflowImportItem
{
    /**
     * return the primary identifier of an import item
     */
    public function getIdentifer();
}