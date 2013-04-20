<?php

use Dat0r\Core\Runtime\Field\ReferenceField;

class SidebarTreeModuleValidator extends AgaviValidator
{
    protected function validate()
    {
        $treeRelationData = $this->getData($this->getArgument());

        if (is_array($treeRelationData) )
        {
            foreach ($treeRelationData as $treeRelation)
            {
                if (
                    isset($treeRelation['treeModule'])
                    && class_exists($treeRelation['treeModule']) 
                    && isset($treeRelation['localModule'])
                    && class_exists($treeRelation['localModule']) 
                    && isset($treeRelation['referenceField'])
                )
                {
                    $moduleClass = $treeRelation['localModule'];
                    $referenceField = $moduleClass::getInstance()->getField(
                        $treeRelation['referenceField']
                    );

                    if (! ($referenceField instanceof ReferenceField))
                    {
                        $this->throwError('type');
                        return FALSE;
                    }
                }
                else
                {
                    $this->throwError('format');
                    return FALSE;
                }
            }
        }
        else
        {
            $this->throwError('format');
            return FALSE;
        }

        $this->export($treeRelationData, $this->getArgument());
        return TRUE;
    }
}

