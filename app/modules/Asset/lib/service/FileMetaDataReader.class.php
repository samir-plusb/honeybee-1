<?php

use Honeybee\Core\Config\IConfig;
use Symfony\Component\Process\Process;

class FileMetaDataReader
{
    const AS_JSON = 'json';

    const AS_TEXT = 'text';

    const AS_XML = 'xml';

    const AS_ARRAY = 'array';

    protected $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    public function read($input_file, $output_type = self::AS_ARRAY, array $property_names = array())
    {
        if (!is_readable($input_file)) {
            throw new \Exception("Unable to read given input_file: " . $input_file);
        }

        $meta_data_output = array();
        switch ($output_type) {
            case self::AS_JSON:
            case self::AS_TEXT:
            case self::AS_XML:
                $meta_data_output = $this->exec($input_file, $output_type);
                break;
            case self::AS_ARRAY:
                $output = $this->exec($input_file, self::AS_JSON);
                $meta_data_output = json_decode($output, true);
                if (!is_array($meta_data_output)) {
                    $meta_data_output = array();
                    $error = $this->getLastJsonErrorAsString();
                    error_log(
                        __METHOD__ .
                        ' Output of apache tika could not be interpreted as valid JSON. ' .
                        ' Error was: ' . $error .
                        ' - Input file was: ' . $input_file
                        // DEBUG: . ' - Output from tika was: ' . $output
                    );
                }
                break;
            default:
                throw new \Exception(
                    sprintf(
                        "Unsupported output type '%s' demanded. Supported are %s ",
                        $output_type,
                        implode(', ', array(self::AS_JSON, self::AS_XML, self::AS_TEXT, self::AS_ARRAY))
                    )
                );
                break;
        }

        return $meta_data_output;
    }

    protected function exec($input_file, $output_type, $tika_options = array())
    {
        // @todo support more options via $tika_options
        $command = sprintf(
            'java -jar %s --encoding=UTF-8 --%s %s',
            $this->config->get('apache_tika_jarfile'),
            $output_type,
            $input_file
        );
        $process = new Process($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    protected function getLastJsonErrorAsString()
    {
        $msg = 'Unknown error';

        // TODO implement PHP v5.5 check to support JSON_ERROR_INF_OR_NAN and JSON_ERROR_UNSUPPORTED_TYPE as well
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $msg = 'No errors (JSON_ERROR_NONE)';
                break;
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded (JSON_ERROR_DEPTH)';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch (JSON_ERROR_STATE_MISMATCH';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found (JSON_ERROR_CTRL_CHAR)';
                break;
            case JSON_ERROR_SYNTAX:
                $msg = 'Syntax error due to malformed JSON (JSON_ERROR_SYNTAX)';
                break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded (JSON_ERROR_UTF8)';
                break;
            default:
                $msg = 'Unknown error (constant unknown)';
                break;
        }

        return $msg;
    }
}
