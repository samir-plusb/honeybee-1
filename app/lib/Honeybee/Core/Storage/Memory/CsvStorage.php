<?php

namespace Honeybee\Core\Storage\Memory;

use Honeybee\Core\Storage;
use Honeybee\Core\Config;

class CsvStorage implements Storage\IStorage
{
    private $stream;

    private $config;

    public function __construct(Config\IConfig $config)
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->config = $config;

        fputcsv(
            $this->stream,
            $this->config->get('csv_headers'),
            $this->config->get('delimiter', ';')
        );
    }

    public function write(array $data)
    {
        fputcsv($this->stream, $data, $this->config->get('delimiter', ';'));
    }

    public function read($key, $revision = NULL)
    {
        rewind($this->stream);
        $foundRow = null;

        while ($curRow = fgetcsv($this->stream))
        {
            if (isset($curRow['identifier']) && $curRow['identifier'] === $key)
            {
                $foundRow = $curRow;
                break;
            }
        }
        fseek($this->stream, 0, SEEK_END);

        return $foundRow;
    }

    public function delete($key, $revision = NULL)
    {
        rewind($this->stream);
        $newStream = fopen('php://memory', 'w+');

        while ($curRow = fgetcsv($this->stream))
        {
            if (!isset($curRow['identifier']) || $curRow['identifier'] !== $key)
            {
                fputcsv($newStream, $curRow);
            }
        }

        $this->stream = $newStream;
    }

    public function getResource()
    {
        return $this->stream;
    }

    public function __destruct()
    {
        @fclose($this->stream);
    }
}
