/**
 * @class
 * @augments midas.core.BaseController
 * @description The EditController serves as the main controller for all Items/EditSuccessView related behaviour.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditController = midas.core.BaseController.extend(
/** @lends midas.items.edit.EditController.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type string
     */
    log_prefix: "EditController",

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(options)
    {
        this.parent(options);
    },

    getIntentFilters: function()
    {
        return {
            '/midas/intents/contentItem/store': this.onStoreContentItemIntent.bind(this),
            '/midas/intents/contentItem/delete': 'onDeleteContentItemIntent'
        };
    },

    /**
     * @description <p>Handles creating new content items.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent to create a new content item.</p>
     */
    onNewContentItemIntent: function()
    {
        this.logDebug("Executing: onNewContentItemIntent");
    },

    /**
     * @description <p>Handles storing a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent to have his editing progress saved.</p>
     */
    onStoreContentItemIntent: function(intent)
    {
        if (intent.data.title === 'foo')
        {
            this.logDebug("Skipping: onStoreContentItemIntent");

            return false;
        }

        this.logDebug("Executing: onStoreContentItemIntent");

        return true;
    },

    /**
     * @description <p>Handles deleting a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to delete a content item.</p>
     */
    onDeleteContentItemIntent: function()
    {
        this.logDebug("Executing: onDeleteContentItemIntent");
    },

    /**
     * @description <p>Handles marking an import item as checked.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to mark an import item.</p>
     */
    onMarkImportItemIntent: function()
    {
        this.logDebug("onMarkImportItemIntent");
    },

    /**
     * @description <p>Handles deleting a impotz item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to delete an import item.</p>
     */
    onDeleteImportItemIntent: function()
    {
        this.logDebug("onDeleteImportItemIntent");
    }
});