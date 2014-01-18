<?php

/**
 * A plugin for the dat0r code-generator.
 * Generates and deploys an elasticsearch index definition for a given module-definition.
 *
 * @todo Atm this class is not namespaced as the dat0r plugin interface does not support namespaces yet.
 * Update as soon as the dat0r codegen ships namespace support for plugins.
 */
class MappingGeneratorPlugin
{
    private $options;

    private $schema;

    private static $typeMap = array(
        'text' => 'string',
        'textarea' => 'string',
        'integer' => 'integer',
        'aggregate' => 'object',
        'reference' => 'object',
        'key-value' => 'object',
        'integer-collection' => 'integer',
        'text-collection' => 'string',
        'select' => 'string',
        'email' => 'string',
        'boolean' => 'boolean'
    );

    public function __construct(array $options = array())
    {
        $this->options = $options;

        $base_dir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        require_once $base_dir . '/config/includes/autoload.php';
    }

    public function execute($moduleSchema)
    {
        $this->schema = $moduleSchema;

        $indexDefinition = array(
            "index_analyzer" => "DefaultAnalyzer",
            "search_analyzer" => "DefaultAnalyzer",
            "dynamic" => FALSE,
            "_source" => array("enabled" => TRUE)
        );

        $defaultProperties = array(
            'identifier' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'revision' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'uuid' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'language' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'version' => array(
                'type' => 'integer'
            ),
            'shortId' => array(
                'type' => 'integer'
            ),
            'slug' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            )
        );

        $moduleDefinition = $moduleSchema->getModuleDefinition();
        foreach ($moduleDefinition->getFields() as $field)
        {
            $handlerFunc = sprintf(
                'map%s',
                implode('', array_map('ucfirst', explode('-', $field->getShortName())))
            );

            if (is_callable(array($this, $handlerFunc)))
            {
                $defaultProperties[$field->getName()] = $this->$handlerFunc($field->getName(), $field, $moduleDefinition);
            }
        }
        $indexDefinition['properties'] = (object)$defaultProperties;

        $deployPath = $this->options['deploy_path'];
        $jsonString = $this->formatJson(json_encode($indexDefinition));
        error_log("deploying to: " . $deployPath);
        file_put_contents($deployPath, $jsonString);

        $this->schema = null;
    }

    protected function mapEmail($fieldName, $field, $moduleDefinition)
    {
        return $this->mapText($fieldName, $field, $moduleDefinition);
    }

    protected function mapSelect($fieldName, $field, $moduleDefinition)
    {
        // @todo if multiple then, return $this->mapTextCollection ...
        return $this->mapText($fieldName, $field, $moduleDefinition);
    }

    protected function mapTextCollection($fieldName, $field, $moduleDefinition)
    {
        $mapping = $this->mapText($fieldName, $field, $moduleDefinition);
        unset($mapping['fields']['sort']);
        unset($mapping['fields']['suggest']);

        return $mapping;
    }

    protected function mapText($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => 'multi_field', 'fields' => array(
            $fieldName => array('type' => $esType),
            'sort' => array(
                'type' => $esType,
                'analyzer' => 'IcuAnalyzer_DE',
                'inlclude_in_all' => FALSE
            ),
            'filter' => array(
                'type' => $esType,
                'index' => 'not_analyzed'
            ),
            'suggest' => array(
                'type' => $esType,
                'analyzer' => 'AutoCompleteAnalyzer',
                'inlclude_in_all' => FALSE
            )
        ));
    }

    protected function mapTextarea($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => $esType);
    }

    protected function mapInteger($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => $esType);
    }

    protected function mapIntegerCollection($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => $esType);
    }

    protected function mapReference($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];
        $properties = array(
            'id' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            ),
            'module' => array(
                'type' => 'string',
                'index' => 'not_analyzed'
            )
        );
        foreach ($field->getOptions()->filterByName('references')->getValue() as $reference_option)
        {
            $index_fields_option = $reference_option->getValue()->filterByName('index_fields');
            if ($index_fields_option)
            {
                $referenced_module_class = $reference_option->getValue()->filterByName('module')->getValue();

                if (!class_exists($referenced_module_class))
                {
                    throw new Exception(
                        sprintf(
                            "Unable to load referenced module '%s' while generating reference index mappings for %s.",
                            $referenced_module_class,
                            $moduleDefinition->getName()
                        )
                    );
                }
                $referenced_module = $referenced_module_class::getInstance();
                foreach ($index_fields_option->getValue()->toArray() as $index_fieldname)
                {
                    $referenced_index_fieldname = $referenced_module->getOption('prefix') . '.' . $index_fieldname;
                    $properties[$referenced_index_fieldname] =  array(
                        'type' => 'multi_field',
                        'fields' => array(
                            $referenced_index_fieldname => array(
                                'type' => 'string'
                            ),
                            'sort' => array(
                                'type' => 'string',
                                'analyzer' => 'IcuAnalyzer_DE',
                                'inlclude_in_all' => FALSE
                            ),
                            'filter' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ),
                            'suggest' => array(
                                'type' => 'string',
                                'analyzer' => 'AutoCompleteAnalyzer',
                                'inlclude_in_all' => FALSE
                            )
                        )
                    );
                }
            }
        }
        return array(
            'type' => $esType,
            'properties' => $properties
        );
    }

    protected function mapBoolean($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => $esType);
    }

    protected function mapKeyValue($fieldName, $field, $moduleDefinition)
    {
        $esType = self::$typeMap[$field->getShortName()];

        return array('type' => $esType, 'dynamic' => TRUE);
    }

    protected function mapAggregate($fieldName, $field, $moduleDefinition)
    {
        $aggregate_classes = $field->getOptions()->filterByName('modules')->getValue()->toArray();

        $parts = explode('\\', $aggregate_classes[0]);
        $aggregateName = str_replace('Module' , '', array_pop($parts));
        $aggregateDefs = $this->schema->getAggregateDefinitions(array($aggregateName));
        $aggregateDef = $aggregateDefs[0];

        $properties = array();
        foreach ($aggregateDef->getFields() as $aggregateField)
        {
            $handlerFunc = sprintf('map%s', ucfirst($aggregateField->getShortName()));
            if (is_callable(array($this, $handlerFunc)))
            {
                $properties[$aggregateField->getName()] = $this->$handlerFunc(
                    $aggregateField->getName(),
                    $aggregateField,
                    $aggregateDef
                );
            }
        }

        $esType = self::$typeMap[$field->getShortName()];

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
