<?php

namespace Honeybee\Core\Storage\Memory;

use Honeybee\Core\Storage;
use Honeybee\Core\Config;
use \XMLWriter;

class XmlStorage implements Storage\IStorage
{
    const OUTPUT_STREAM = 'output_stream';

    const MEMORY_STREAM = 'memory_stream';

    private $config;

    private $xml_writer;

    public function __construct(Config\IConfig $config)
    {
        $this->config = $config;
        $this->xml_writer = $this->createXmlWriter();
        $this->xml_writer->startElement('documents');
    }

    public function write(array $data)
    {
        $this->writeXmlData('document', $data);
    }

    public function read($key, $revision = NULL)
    {
    }

    public function delete($key, $revision = NULL)
    {
    }

    public function getResource()
    {
        return $this->xml_writer;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function writeXmlData($node_name, array $data)
    {
        $this->xml_writer->startElement($node_name);
        foreach ($data as $key => $value) {
            $child_element_name = $key;
            if(is_numeric($key)) {
                $child_element_name = \AgaviInflector::singularize($node_name);
            }
            if (is_array($value)) {
                $this->writeXmlData($child_element_name, $value);
            } else {
                $this->xml_writer->startElement($child_element_name);
                $this->xml_writer->text(htmlspecialchars(trim($value)));
                $this->xml_writer->endElement();
            }
        }
        $this->xml_writer->endElement();
    }

    protected function createXmlWriter()
    {
        $xml_writer = new XMLWriter();

        if ($this->config->get('write_to') === self::OUTPUT_STREAM) {
            $xml_writer->openUri('php://output');
        } else {
            $xml_writer->openUri('php://memory');
        }

        $xml_writer->startDocument('1.0', 'UTF-8');
        $xml_writer->setIndent(true);
        $xml_writer->setIndentString("  ");

        return $xml_writer;
    }
}
