<?php

/**
 * The IContentItem interface defines the data structure that is used to hold our rich content
 * as a result of the editing-department's INewsEntity refinement process.
 * It holds all data that will be available to the consumers that are targeted by the following content distribution.
 * IContentItems are aggregated by an IWorkflowItem (aggregate root) and may not exist on their own.
 * In most cases they are created(updated) when an editor opens an IWorkflowItem to refine or update imported data,
 * which will have it's seeds in the adjacent INewsEntity.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package News
 * @subpackage Workflow/Item
 */
interface IContentItem extends IDocument
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
}

?>
