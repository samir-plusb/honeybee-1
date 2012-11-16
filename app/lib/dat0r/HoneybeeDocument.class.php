<?php

use Dat0r\Core\Runtime\Document\Document;

abstract class HoneybeeDocument extends Document
{
    public function setIdentifier($identifier)
    {
        $this->setValue('identifier', $identifier);
    }

    public function getIdentifier()
    {
        return $this->getValue('identifier');   
    }

    public function setRevision($revision)
    {
        $this->setValue('revision', $revision);
    }

    public function getRevision()
    {
        return $this->getValue('revision');
    }
}
