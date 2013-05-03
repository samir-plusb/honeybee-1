<?php
/*              AUTOGENERATED CODE - DO NOT EDIT !
This base class was generated by the Dat0r library (https://github.com/berlinonline/Dat0r) 
on 2013-04-28 00:55:58 and is closed to modifications by any meaning.
If you are looking for a place to alter the behaviour of the 'WorkflowTicket' module,
then the 'WorkflowTicketModule' (skeleton) class probally might be a good place to look. */

namespace Honeybee\Domain\User\Base;

/**
 * Serves as the base class to the 'WorkflowTicket'' module skeleton.
 */
abstract class WorkflowTicketModule extends \Dat0r\Core\Runtime\Module\AggregateModule
{
    /**
     * Creates a new WorkflowTicketModule instance.
     */
    protected function __construct()
    {
        parent::__construct('WorkflowTicket', array( 
            \Dat0r\Core\Runtime\Field\TextField::create('workflowName'), 
            \Dat0r\Core\Runtime\Field\TextField::create('workflowStep'), 
            \Dat0r\Core\Runtime\Field\TextField::create('owner'), 
            \Dat0r\Core\Runtime\Field\BooleanField::create('blocked'), 
            \Dat0r\Core\Runtime\Field\KeyValueField::create('stepCounts', array(                  
                'constraints' => array('value_type' => 'integer',),  
            )), 
            \Dat0r\Core\Runtime\Field\TextField::create('waitUntil'), 
            \Dat0r\Core\Runtime\Field\AggregateField::create('lastResult', array(                  
                'aggregate_module' => 'Honeybee\\Domain\\User\\PluginResultModule',  
            )), 
        ), array(             
            'baseDocument' => '\\Honeybee\\Core\\Dat0r\\WorkflowTicket',   
        ));
    }

    /**
     * Returns the IDocument implementor to use when creating new documents.
     *
     * @return string Fully qualified name of an IDocument implementation.
     */
    protected function getDocumentImplementor()
    {
        return 'Honeybee\Domain\User\WorkflowTicketDocument';
    }
}