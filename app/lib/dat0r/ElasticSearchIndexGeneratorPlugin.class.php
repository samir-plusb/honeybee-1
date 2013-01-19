<?php

/**
 * A plugin for the dat0r code-generator.
 * Generates and deploys an elasticsearch index definition for a given module-definition.
 */
class ElasticSearchIndexGeneratorPlugin
{
    private $options;

    private static $typeMap = array(
        'text' => 'string',
        'integer' => 'integer',
        'aggregate' => 'object',
        'key-value' => 'object',
        'integer-collection' => 'integer',
        'boolean' => 'boolean'
    );

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function execute($moduleDefinition)
    {
        $indexDefinition = array(
            "index_analyzer" => "default",
            "search_analyzer" => "default",
            "dynamic" => FALSE,
            "_source" => array("enabled" => TRUE)
        );

        $properties = array(
            'identifier' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'revision' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            )
        );
        foreach ($moduleDefinition->getFields() as $name => $field)
        {
            $handlerFunc = sprintf(
                'map%s', 
                implode('', array_map('ucfirst', explode('-', $field['type'])))
            );

            if (is_callable(array($this, $handlerFunc)))
            {
                $properties[$name] = $this->$handlerFunc($name, $field, $moduleDefinition);
            }
        }
        $indexDefinition['properties'] = (object)$properties;

        $deployPath = $this->options['deployPath'];
        if (0 !== strpos($deployPath, DIRECTORY_SEPARATOR))
        {
            $deployPath = Dat0r\Core\CodeGenerator\Configuration::normalizePath(
                $this->options['basePath'] . DIRECTORY_SEPARATOR . $deployPath
            );
        }

        $jsonString = $this->formatJson(json_encode($indexDefinition));
        file_put_contents($deployPath, $jsonString);
    }

    protected function mapText($fieldName, array $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field['type']];

        return array('type' => 'multi_field', 'fields' => array(
            $fieldName => array('type' => $esType),
            sprintf("%s.raw", $fieldName) => array(
                'type' => $esType, 
                'index' => 'not_analyzed'
            )
        ));
    }

    protected function mapInteger($fieldName, array $field, $moduleDefinition)
    {   
        $esType = self::$typeMap[$field['type']];

        return array('type' => $esType);
    }

    protected function mapIntegerCollection($fieldName, array $field, $moduleDefinition)
    {   
        $esType = self::$typeMap[$field['type']];

        return array('type' => $esType);
    }

    protected function mapBoolean($fieldName, array $field, $moduleDefinition)
    {   
        $esType = self::$typeMap[$field['type']];

        return array('type' => $esType);
    }

    protected function mapKeyValue($fieldName, array $field, $moduleDefinition)
    {   
        $esType = self::$typeMap[$field['type']];

        return array('type' => $esType, 'dynamic' => TRUE);
    }

    protected function mapAggregate($fieldName, array $field, $moduleDefinition)
    {
        $parts = explode('\\', $field['options']['aggregate_module']);
        $aggregateName = str_replace('Module' , '', array_pop($parts));
        $aggregates = $moduleDefinition->getAggregates();

        if (! isset($aggregates[$aggregateName]))
        {
            throw new Exception(
                "Unable to find aggregate definition for $aggregateName."
            );
        }

        $properties = array();
        $aggregateDef = $aggregates[$aggregateName];
        foreach ($aggregateDef->getFields() as $fieldname => $aggregateField)
        {
            $handlerFunc = sprintf('map%s', ucfirst($aggregateField['type']));
            if (is_callable(array($this, $handlerFunc)))
            {
                $properties[$fieldname] = $this->$handlerFunc(
                    $fieldname, 
                    $aggregateField, 
                    $aggregateDef
                );
            }
        }

        $esType = self::$typeMap[$field['type']];

        return array('type' => $esType, 'properties' => (object)$properties);
    }

    // copy & paste from here:
    // http://recursive-design.com/blog/2008/03/11/format-json-with-php/
    protected function formatJson($json) 
    {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i <= $strLen; $i++) 
        {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);
            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') 
            {
                $outOfQuotes = !$outOfQuotes;
                // If this character is the end of an element, 
                // output a new line and indent the next line.
            } 
            else if(($char == '}' || $char == ']') && $outOfQuotes) 
            {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) 
                {
                    $result .= $indentStr;
                }
            }
            // Add the character to the result string.
            $result .= $char;
            // If the last character was the beginning of an element, 
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) 
            {
                $result .= $newLine;
                if ($char == '{' || $char == '[') 
                {
                    $pos ++;
                }
                for ($j = 0; $j < $pos; $j++) 
                {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }

        return $result;
    }
}
