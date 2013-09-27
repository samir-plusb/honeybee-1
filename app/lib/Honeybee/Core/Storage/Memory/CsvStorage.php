<?php

namespace Honeybee\Core\Storage\Memory;

use Honeybee\Core\Storage;
use Honeybee\Core\Config;

class CsvStorage implements Storage\IStorage
{
    const OUTPUT_STREAM = 'output_stream';

    const MEMORY_STREAM = 'memory_stream';

    private $stream;

    private $config;

    public function __construct(Config\IConfig $config)
    {
        $this->config = $config;
        $this->stream = $this->openStream();

        fputcsv(
            $this->stream,
            $this->config->get('csv_headers'),
            $this->config->get('delimiter', ';')
        );
    }

    public function write(array $data)
    {
        $delimiter = $this->config->get('delimiter', ';');
        fputcsv($this->stream, $data, $delimiter);
    }

    public function read($key, $revision = NULL)
    {
        $found_row = null;

        while ($cur_row = fgetcsv($this->stream)) {
            if (isset($cur_row['identifier']) && $cur_row['identifier'] === $key) {
                $found_row = $cur_row;
                break;
            }
        }
        fseek($this->stream, 0, SEEK_END);

        return $found_row;
    }

    public function delete($key, $revision = NULL)
    {
        rewind($this->stream);
        $new_stream = $this->openStream();

        while ($cur_row = fgetcsv($this->stream)) {
            if (!isset($cur_row['identifier']) || $cur_row['identifier'] !== $key) {
                fputcsv($new_stream, $cur_row);
            }
        }

        $this->stream = $new_stream;
    }

    public function getResource()
    {
        return $this->stream;
    }

    public function __destruct()
    {
        @fclose($this->stream);
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function openStream()
    {
        if ($this->config->get('write_to') === self::OUTPUT_STREAM) {
            return fopen('php://output', 'w+');
        } else {
            return fopen('php://memory', 'w+');
        }
    }
}
