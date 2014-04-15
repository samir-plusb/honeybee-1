<?php

namespace Honeybee\Core\Export;

use Honeybee\Core\Dat0r\Document;
use DateTime;

class DocumentExport extends GenericExport
{
    const PUBLISHED_AT_FIELD = 'publishedAt';

    public function revoke(Document $document)
    {
        $identifier = $this->getStorageIdentifier($document);

        if ($data = $this->storage->read($identifier)) {
            $identifier = $data['identifier'];
            $revision = $data['revision'];
            $this->storage->delete($identifier, $revision);

            foreach ($this->filters as $filter) {
                $filter->onDocumentRevoked($document);
            }
        }
    }

    protected function buildExportData(Document $document)
    {
        $meta_data = $document->getMeta();
        if (!isset($meta_data[self::PUBLISHED_AT_FIELD])) {
            $publish_date = new DateTime();
            $meta_data[self::PUBLISHED_AT_FIELD] = $publish_date->format(DATE_ISO8601);
            $document->setMeta($meta_data);
        }

        $data = parent::buildExportData($document);

        $export_identifier = $this->getStorageIdentifier($document);
        $data['identifier'] = $export_identifier;
        $data['type'] = $document->getModule()->getOption('prefix');
        $data[self::PUBLISHED_AT_FIELD] = $meta_data[self::PUBLISHED_AT_FIELD];

        if ($this->storage && $prev_data = $this->storage->read($export_identifier)) {
            $data['revision'] = $prev_data['revision'];
        }

        return $data;
    }

    protected function getStorageIdentifier(Document $document)
    {
        return $document->getShortIdentifier();
    }
}
