<?php

namespace Honeybee\Core\Storage\Memory;

use Honeybee\Core\Storage;
use Honeybee\Core\Config;

class XmlZipArchiveStorage implements Storage\IStorage
{
    private $zip_archive;

    private $config;

    private $tmp_path;

    public function __construct(Config\IConfig $config)
    {
        $this->config = $config;

        $this->tmp_path = \AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . md5(microtime());
        if (!mkdir($this->tmp_path))
        {
            throw new \Exception("Unable to create tmp-directory for zip-archive.");
        }

        $zipfile_path = $this->tmp_path . DIRECTORY_SEPARATOR . $this->config->get('archive_name', 'book-list.zip');
        $this->zip_archive = new ZipArchive();
        if (true !== $this->zip_archive->open($zipfile_path, ZipArchive::CREATE))
        {
            throw new \Exception("Unable to create/open zip archive.");
        }
    }

    public function write(array $data)
    {
        $dom_doc = new \DOMDocument('1.0', 'utf-8');
        $dom_doc->appendChild(
            $this->createDomElementFromArray($dom_doc, 'document', $data)
        );
        $dom_doc->formatOutput = true;

        $file_pattern = $this->config->get('file_pattern');
        $search = array();
        $replace = array();

        if (preg_match_all('~(\{([\w_\-0-9]+)\})+~is', $file_pattern, $matches))
        {
            foreach ($matches[2] as $index => $fieldname)
            {
                $search[] = $matches[1][$index];
                $replace[] = $data[$fieldname];
            }
        }

        $internal_archive_path = sprintf(
            '%s/%s',
            $this->config->get('archive_base_path', 'XML-Files'),
            str_replace($search, $replace, $file_pattern)
        );

        $this->zip_archive->addFromString($internal_archive_path, $dom_doc->saveXML());
    }

    public function read($key, $revision = NULL)
    {
    }

    public function delete($key, $revision = NULL)
    {
    }

    public function getResource()
    {
        return $this->zip_archive;
    }

    protected function createDomElementFromArray(\DOMDocument $dom_doc, $node_name, array $data)
    {
        $element = $dom_doc->createElement($node_name);
        foreach ($data as $key => $value)
        {
            $child_element = NULL;
            $child_element_name = $key;
            if(is_numeric($child_element_name))
            {
                $child_element_name = \AgaviInflector::singularize($node_name);
            }
            if (is_array($value))
            {
                $child_element = $this->createDomElementFromArray($dom_doc, $child_element_name, $value);
            }
            else
            {
                $child_element = $dom_doc->createElement($child_element_name);
                $child_element->nodeValue = htmlspecialchars(trim($value));
            }
            $element->appendChild($child_element);
        }

        return $element;
    }

    public function __destruct()
    {
        @unlink($this->zip_archive->getArchivePath());
        @rmdir($this->tmp_path);
    }
}
