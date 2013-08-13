<?php
/*              AUTOGENERATED CODE - DO NOT EDIT !
This base class was generated by the Dat0r library (https://github.com/berlinonline/Dat0r)
on 2013-08-05 22:31:14 and is closed to modifications by any meaning.
If you are looking for a place to alter the behaviour of the 'PluginResult' module,
then the 'PluginResultModule' (skeleton) class probally might be a good place to look. */

namespace Honeybee\Domain\User\Base;

use Dat0r\Core\Field\IntegerField;
use Dat0r\Core\Field\TextField;
use Dat0r\Core\Module;

/**
 * Serves as the base class to the 'PluginResult'' module skeleton.
 */
abstract class PluginResultModule extends Module\AggregateModule
{
    /**
     * Creates a new PluginResultModule instance.
     */
    protected function __construct()
    {
        parent::__construct(
            'PluginResult',
            array(
                IntegerField::create(
                    'state',
                    array()
                ),
                TextField::create(
                    'gate',
                    array()
                ),
                TextField::create(
                    'message',
                    array()
                ),
            ),
            array()
        );
    }

    /**
     * Returns the IDocument implementor to use when creating new documents.
     *
     * @return string Fully qualified name of an IDocument implementation.
     */
    protected function getDocumentImplementor()
    {
        return '\\Honeybee\\Domain\\User\\PluginResultDocument';
    }
}
