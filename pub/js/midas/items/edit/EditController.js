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

    /**
     * @description Return an object describing the filters we provide for capturing intents.
     */
    getIntentFilters: function()
    {
        return {
            '/midas/intents/contentItem/store': this.onStoreContentItemIntent.bind(this),
            '/midas/intents/contentItem/delete': 'onDeleteContentItemIntent',
            '/midas/intents/importItem/mark': 'onMarkImportItemIntent'
        };
    },

    /**
     * @description <p>Handles storing a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent to have his editing progress saved.</p>
     */
    onStoreContentItemIntent: function(intent, callback)
    {
        $.post(intent.target_uri, intent.data, function()
        {
            callback();
        }, 'json');

        return true;
    },

    /**
     * @description <p>Handles deleting a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to delete a content item.</p>
     */
    onDeleteContentItemIntent: function(intent, callback)
    {
        $.post(intent.target_uri, intent.data, function()
        {
            if (callback)
            {
                callback();
            }
        }, 'json');

        return true;
    },

    /**
     * @description <p>Handles marking an import item as checked.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to mark an import item.</p>
     */
    onMarkImportItemIntent: function(intent, callback)
    {
        this.logDebug("onMarkImportItemIntent");
        
        $.post(intent.target_uri, intent.data, function()
        {
            if (callback)
            {
                callback();
            }
        }, 'json');

        return true;
    },

    /**
     * @description <p>Handles deleting a impotz item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting an users intent of wanting to delete an import item.</p>
     */
    onDeleteImportItemIntent: function(intent, callback)
    {
        this.logDebug("onDeleteImportItemIntent");
    }
});