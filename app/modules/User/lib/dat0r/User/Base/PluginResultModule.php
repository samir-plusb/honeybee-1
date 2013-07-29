<?php
/*              AUTOGENERATED CODE - DO NOT EDIT !
This base class was generated by the Dat0r library (https://github.com/berlinonline/Dat0r) 
on 2013-04-28 00:55:58 and is closed to modifications by any meaning.
If you are looking for a place to alter the behaviour of the 'PluginResult' module,
then the 'PluginResultModule' (skeleton) class probally might be a good place to look. */

namespace Honeybee\Domain\User\Base;

/**
 * Serves as the base class to the 'PluginResult'' module skeleton.
 */
abstract class PluginResultModule extends \Dat0r\Core\Module\AggregateModule
{
    /**
     * Creates a new PluginResultModule instance.
     */
    protected function __construct()
    {
        parent::__construct('PluginResult', array( 
            \Dat0r\Core\Field\IntegerField::create('state'), 
            \Dat0r\Core\Field\TextField::create('gate'), 
            \Dat0r\Core\Field\TextField::create('message'), 
        ));
    }

    /**
     * Returns the IDocument implementor to use when creating new documents.
     *
     * @return string Fully qualified name of an IDocument implementation.
     */
    protected function getDocumentImplementor()
    {
        return 'Honeybee\Domain\User\PluginResultDocument';
    }
}
