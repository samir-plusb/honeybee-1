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
            $identifier = $data['identifier'];
            $revision = $data['revision'];
            $this->storage->delete($identifier, $revision);

            foreach ($this->filters as $filter)
            {
                $filter->onDocumentRevoked($document);
            }
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

        $export_identifier = $document->getShortIdentifier();
        $data['identifier'] = $export_identifier;
        $data['type'] = $document->getModule()->getOption('prefix');
        $data[self::PUBLISHED_AT_FIELD] = $metaData[self::PUBLISHED_AT_FIELD];

        if ($prev_data = $this->storage->read($export_identifier))
        {
            $data['revision'] = $prev_data['revision'];
        }

        return $data;
    }
}
