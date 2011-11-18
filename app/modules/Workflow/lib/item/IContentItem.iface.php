<?php

/**
 * The IContentItem interface defines the data structure that is used to hold our rich content
 * as a result of the editing-department's IImportItem refinement process.
 * It holds all data that will be available to the consumers that are targeted by the following content distribution.
 * IContentItems are aggregated by an IWorkflowItem (aggregate root) and may not exist on their own.
 * In most cases they are created(updated) when an editor opens an IWorkflowItem to refine or update imported data,
 * which will have it's seeds in the adjacent IImportItem.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
interface IContentItem
{
    /**
     * Returns the unique identifier of our aggregate root (IWorkflowItem).
     *
     * @return string
     *
     * @see IWorkflowItem::getIdentifier()
     */
    public function getParentIdentifier();

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item was created.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-23-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getCreated();

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item modified the last time.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * <pre>
     * Value structure example:
     * array(
     *     'date' => '05-25-1985T15:23:78.123+01:00',
     *     'user' => 'shrink0r'
     * )
     * </pre>
     *
     * @return array
     */
    public function getLastModified();

    /**
     * Returns the IContentItem's type.
     * Relates to the content's origin like: dpa-regio or rss.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the IContentItem's priority.
     * The priority defines how important this item is compared to others.
     * May be an int from 1-3.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Returns the IContentItem's category.
     * Will be something relating to the kind of content which is contained.
     * Probally somthing like: 'Kiezleben', 'Polizeimeldung' or 'Kultur' ...
     *
     * @return string
     */
    public function getCategory();

    /**
     * Holds the content's title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Holds the content's teaser text.
     *
     * @return string
     */
    public function getTeaser();

    /**
     * Returns the main content text.
     *
     * @return string
     */
    public function getText();

    /**
     * Returns content-items source.
     * Holds data akin to the type attribute,
     * with the difference that the type attribute is set by the system
     * whereas the source may be set by an editor.
     *
     * @return string
     */
    public function getSource();

    /**
     * Returns a valid url using the http or https scheme
     * and that shall be linked with the content.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Returns an array that represents a date interval,
     * holding an ISO8601 UTC formatted date string for two keys "from" and "untill".
     *
     * <pre>
     * Value structure example:
     * array(
     *     'from'   => '05-25-1985T15:23:78.123+01:00',
     *     'untill' => '05-25-1985T15:23:78.123+01:00'
     * )
     * </pre>
     *
     * @return array
     */
    public function getDate();

    /**
     * Returns an array representation of the IContentItem.
     *
     * <pre>
     * Example value structure:
     * array(
     *  // Meta Data
     *  'parentIdentifier'    => 'foobar',
     *  'created'             => array(
     *      'date' => '05-23-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *   ),
     *   'lastModified'       => array(
     *      'date' => '06-25-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *    ),
     *    // Content Data
     *    'type'              => 'mail',
     *    'priority'          => 2,
     *    'title'             => 'Neue Termine: 42 for is the answer',
     *    'text'              => 'Der Verein ist ein Verein',
     *    'teaser'            => 'and the teaser will get u to read the text',
     *    'category'          => 'Kiezleben',
     *    'source'            => 'Bezirksamt Pankow',
     *    'url'               => 'http://www.lookmomicanhazurls.com',
     *    'isevent'           => FALSE,
     *    'affects_wholecity' => FALSE,
     *    'relevance'         => 0,
     *    'date'              => array(
     *        'from'   => '05-23-1985T15:23:78.123+01:00',
     *        'untill' => '05-25-1985T15:23:78.123+01:00'
     *     ),
     *     'location'         => array(
     *         'coords'                   => array(
     *             'long' => '12.19281',
     *             'lat'  => '13.2716'
     *          ),
     *          'city'                    => 'Berlin',
     *          'postal_code'             => '13187',
     *          'administrative_district' => 'Pankow',
     *          'district'                => 'Prenzlauer Berg',
     *          'neighborhood'            => 'Niederschönhausen',
     *          'street'                  => 'Shrinkstreet',
     *          'house_num'               => '23',
     *          'name'                    => 'Vereinsheim Pankow - Niederschönhausen'
     *     )
     * )
     * </pre>
     *
     * @return array
     */
    public function toArray();
}

?>
