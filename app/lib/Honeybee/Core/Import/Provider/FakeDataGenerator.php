<?php

namespace Honeybee\Core\Import\Provider;

use \Dat0r\Core\Module\IModule;
use \Dat0r\Core\Document\IDocument;
use \Dat0r\Core\Field\IField;
use \Dat0r\Core\Sham\DataGenerator;

/**
 * The FakeDataGenerator extends the Dat0r Sham\DataGenerator to handle
 * references a bit more sophisticated (that is, store them).
 */
class FakeDataGenerator extends DataGenerator
{
    /**
     * Generates and adds fake data for a ReferenceField on a document and
     * stores the newly generated reference documents.
     *
     * @param IDocument $document an instance of the document to fill with fake data.
     * @param IField $field an instance of the ReferenceField to fill with fake data.
     * @param array $options array of options to customize fake data creation.
     *
     * @return void
     */
    protected function addReference(IDocument $document, IField $field, array $options = array())
    {
        $recursion_level = 1;
        if (!empty($options[self::OPTION_RECURSION_LEVEL]) && is_int($options[self::OPTION_RECURSION_LEVEL]))
        {
            $recursion_level = $options[self::OPTION_RECURSION_LEVEL];
        }

        if ($recursion_level > 1)
        {
            return;
        }

        $options_clone = $options;
        $options_clone[self::OPTION_RECURSION_LEVEL] = $recursion_level + 1;

        $referencedModules = $field->getReferencedModules();
        $collection = $field->getDefaultValue();

        $numberOfReferencedModules = count($referencedModules);
        $numberOfNewReferenceEntries = $this->faker->numberBetween(1, 3);

        // add number of documents to reference depending on number of referenced modules
        for ($i = 0; $i < $numberOfReferencedModules; $i++)
        {
            $numberOfNewReferenceEntries += $this->faker->numberBetween(0, 3);
        }

        // add new documents to collection for referenced modules
        for ($i = 0; $i < $numberOfNewReferenceEntries; $i++)
        {
            $ref_module = $this->faker->randomElement($referencedModules);
            $new_document = $this->createFakeDocument($ref_module, $options_clone);
            $ref_module->getRepository()->write($new_document);
            $collection->add($new_document);
        }

        $this->setValue($document, $field, $collection, $options);
    }
}
