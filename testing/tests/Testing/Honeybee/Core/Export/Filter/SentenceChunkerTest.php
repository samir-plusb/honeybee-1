<?php

namespace Testing\Honeybee\Core\Export\Filter;

use Testing\Honeybee\Core\BaseTest;
use AgaviConfig;
use Honeybee\Core\Export\Filter\SentenceChunker;
use Honeybee\Core\Config\ArrayConfig;

class SentenceChunkerTest extends BaseTest
{
    protected $chunker;

    public function setUp()
    {
        $this->chunker = new SentenceChunker(
            new ArrayConfig(
                array('dot_tokens_file' => __DIR__ . '/Fixtures/dot_tokens.txt')
            )
        );
    }

    /**
     * @dataProvider provideChunk
     */
    public function testChunk($text, $sentences)
    {
        $chunks = $this->chunker->chunk($text);
        $this->assertEquals($sentences, $chunks);
    }

    /**
     * @dataProvider provideFindEndOfFirstSentence
     */
    public function testFindEndOfFirstSentence($text, $expected_offset)
    {
        $offset = $this->chunker->findEndOfFirstSentence($text);
        $this->assertEquals($expected_offset, $offset);
    }

    public function provideChunk()
    {
        return array(
            array(
                'Die Schulzeit ist zu Ende – und jetzt? Der Übergang von der Schule in den Beruf ist ein großer Schritt. Und dieser will gut überlegt sein. Junge Menschen sollten sich umfassend informieren und Beratungsangebote wahrnehmen. Besonders wertvoll für den Entscheidungsprozess sind eigene Erfahrungen, zum Beispiel in Form von Praktika, einem Freiwilligendienst oder Auslandsaufenthalt.',
                array(
                    'Die Schulzeit ist zu Ende – und jetzt?',
                    'Der Übergang von der Schule in den Beruf ist ein großer Schritt.',
                    'Und dieser will gut überlegt sein.',
                    'Junge Menschen sollten sich umfassend informieren und Beratungsangebote wahrnehmen.',
                    'Besonders wertvoll für den Entscheidungsprozess sind eigene Erfahrungen, zum Beispiel in Form von Praktika, einem Freiwilligendienst oder Auslandsaufenthalt.'
                )
            ),
            array(
                'Wenn die Wohnung zu eng oder die Miete zu teuer wird, dann heißt es umziehen. Für Familien mit geringem Einkommen gibt es Hilfen, um an bezahlbare Wohnungen zu kommen. Und bei Ärger mit dem Vermieter/der Vermieterin oder mit Nachbarn/Nachbarinnen, kann eine Beratung bei einem Mieterverein helfen.',
                array(
                    'Wenn die Wohnung zu eng oder die Miete zu teuer wird, dann heißt es umziehen.',
                    'Für Familien mit geringem Einkommen gibt es Hilfen, um an bezahlbare Wohnungen zu kommen.',
                    'Und bei Ärger mit dem Vermieter/der Vermieterin oder mit Nachbarn/Nachbarinnen, kann eine Beratung bei einem Mieterverein helfen.'
                )
            ),
            array(
                'Wenn das Kind oder erwachsene Angehörige schwer erkranken, stellt dies Familien neben der emotionalen Belastung auch vor große organisatorische Probleme. Verschiedene Hilfs- und Beratungsangebote helfen Pflegenden, das Beste aus der schwierigen Situation zu machen.',
                array(
                    'Wenn das Kind oder erwachsene Angehörige schwer erkranken, stellt dies Familien neben der emotionalen Belastung auch vor große organisatorische Probleme.',
                    'Verschiedene Hilfs- und Beratungsangebote helfen Pflegenden, das Beste aus der schwierigen Situation zu machen.'
                )
            )
        );
    }

    public function provideFindEndOfFirstSentence()
    {
        return array(
            array(
                'Die Schulzeit ist zu Ende – und jetzt? Der Übergang von der Schule in den Beruf ist ein großer Schritt. Und dieser will gut überlegt sein. Junge Menschen sollten sich umfassend informieren und Beratungsangebote wahrnehmen. Besonders wertvoll für den Entscheidungsprozess sind eigene Erfahrungen, zum Beispiel in Form von Praktika, einem Freiwilligendienst oder Auslandsaufenthalt.',
                37
            ),
            array(
                'Wenn die Wohnung zu eng oder die Miete zu teuer wird, dann heißt es umziehen. Für Familien mit geringem Einkommen gibt es Hilfen, um an bezahlbare Wohnungen zu kommen. Und bei Ärger mit dem Vermieter/der Vermieterin oder mit Nachbarn/Nachbarinnen, kann eine Beratung bei einem Mieterverein helfen.',
                76
            ),
            array(
                'Wenn die Wohnung zu eng oder die Miete zu teuer wird, oh je! Für Familien mit geringem Einkommen gibt es Hilfen, um an bezahlbare Wohnungen zu kommen. Und bei Ärger mit dem Vermieter/der Vermieterin oder mit Nachbarn/Nachbarinnen, kann eine Beratung bei einem Mieterverein helfen.',
                59
            ),
            array(
                'Dr. Smith wanted to be a prof. baller, since he was a small kid.',
                63
            ),
            array(
                'Und dieser will gut überlegt sein. Junge Menschen sollten sich umfassend informieren und Beratungsangebote wahrnehmen. Besonders wertvoll für den Entscheidungsprozess sind eigene Erfahrungen, zum Beispiel in Form von Praktika, einem Freiwilligendienst oder Auslandsaufenthalt.',
                33
            )
        );
    }
}
