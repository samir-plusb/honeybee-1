<?php

/**
 * The IWorkflowItem interface defines the data structure that is pushed through the workflow
 * in order to refine and distribute our companies (imported) content.
 * The latter data seperates into four main sections that are aggregated by this interface,
 * hence it serves as the aggregate root for the underlying domain data structure.
 * The following entities are nested into a IWorkflowItem:
 *
 * - ImportItem: Holds the raw imported data together with some meta infos about the data's lifecycle and origin.
 *               An ImportItem is never modified from outside the import and after it is initially created,
 *               it may be updated in cases where the delivering content provider supports/sends updates.
 *               Updates are reflected by a difference between the values for the created and the modified fields.
 *
 * - ContentItems: A collection of different data units that have been gained from processing an ImportItem.
 *                 These reflect the data that actually produces our companies ROI and are mainly created/refined by
 *                 real life editors that work themselves through the workflow system until the purified content items
 *                 are distributed to the targeted consumers.
 *
 * - Attributes: A genric collection of arbitary key=>value pairs,
 *               that can be used to augment/expand the defined set of domain data without violating the interface.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Import
 * @subpackage Base
 */
interface IWorkflowItem
{
    /**
     * Return our related import item.
     *
     * @return array
     *
     * The returned structure looks as follows:
        array(
            'identifier' => 'foobar',
            'created'    => '05-23-1985T15:23:78.123+01:00',
            'modified'   => '05-23-1985T15:23:78.123+01:00',
            'source'     => 'dpa-regio',
            'origin'     => 'tmp/dpa/regio/bla.fasel',
            'timestamp'  => 1234567876543210,
            'title'      => '42 for is the answer',
            'content'    => 'and 23 is the question to answer the content...',
            'category'   => '/for/category/path/u/know',
            'media'      => array(1, 2, 3), // An array of id's that can be used against the ProjectAssetService.
            'geo_data'   => array(
                'long' => 12.19281,
                'lat'  => 13.2716
            )
        );
     */
    public function getImportItem();

    /**
     * Return a list of content items that belong to this workflow item.
     *
     * @return array
     *
     * The returned structure looks as follows:
        array(
            0 => array(
                // Meta Data
                'parent'     => 'foobar',
                'created'    => array(
                    'time' => '05-23-1985T15:23:78.123+01:00',
                    'user' => 'shrink0r'
                ),
                'modified'    => array(
                    'time' => '06-25-1985T15:23:78.123+01:00',
                    'user' => 'shrink0r'
                ),
                'state'      => 'live',
                // Content Data
                'source'     => 'Bezirksamt Pankow',
                'priority'   => 2,
                'title'      => 'Neue Termine: 42 for is the answer',
                'text'       => 'Der Verein ist ein Verein',
                'teaser'     => 'and the teaser will get u to read the text',
                'category'   => 'Kiezleben',
                'url'        => 'http://www.lookmomicanhazurls.com',
                'date'       => array(
                    'from'    => '05-23-1985T15:23:78.123+01:00',
                    'untill'  => '05-25-1985T15:23:78.123+01:00',
                    'isevent' => FALSE
                ),
                'location'   => array(
                    'coords' => array(
                        'long' => '12.19281',
                        'lat'  => '13.2716',
                    ),
                    'adress' => array(
                        'city'        => 'Berlin',
                        'postal_code' => 13187,
                        'street'      => 'Shrinkstreet',
                        'house_num'   => 23
                    ),
                    'name'              => 'Vereinsheim Pankow - Niederschönhausen',
                    'neighborhood'      => 'Pankow',
                    'subneighborhood'   => 'Niederschönhausen',
                    'affects_wholecity' => FALSE,
                    'relevance'         => 0
                )
            ),
            1 => array(...),
            ...
        );
     */
    public function getContentItems();

    /**
     * Return a generic assoc array of attributes.
     *
     * @return array Plain key=>value collection.
     */
    public function getAttributes();
}

?>
