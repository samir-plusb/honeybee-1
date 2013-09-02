<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;

class DocumentExport extends GenericExport
{
    const PUBLISHED_AT_FIELD = 'publishedAt';

    public function revoke(Document $document)
    {
        $identifier = $document->getShortIdentifier();

        if ($data = $this->storage->read($identifier))
        {
            parent::revoke($document);
        }
    }

    protected function buildExportData(Document $document)
    {
        $metaData = $document->getMeta();
        if (! isset($metaData[self::PUBLISHED_AT_FIELD]))
        {
            $publishDate = new \DateTime();
            $metaData[self::PUBLISHED_AT_FIELD] = $publishDate->format(DATE_ISO8601);
            $document->setMeta($metaData);
        }

        $data = parent::buildExportData($document);
        $data['identifier'] = $document->getShortIdentifier();
        $data['type'] = $document->getModule()->getOption('prefix');
        $data[self::PUBLISHED_AT_FIELD] = $metaData[self::PUBLISHED_AT_FIELD];

        return $data;
    }
}
