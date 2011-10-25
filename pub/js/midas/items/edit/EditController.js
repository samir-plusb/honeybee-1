/**
 * @class
 * @augments midas.core.BaseObject
 * @description The EditController serves as the main controller for all Items/EditSuccessView related behaviour.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditController = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditController.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type string
     */
    log_prefix: "EditController",

    /**
     * Holds our view instance.
     * @type midas.items.edit.EditView
     */
    view: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function()
    {
        this.view = new midas.items.edit.EditView(
            this,
            $(document.body)[0], {
                'tabs_container': '.item-content'
            }
        );
    },

    /**
     * @description <p>Handles creating new content items.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting a users intent to create a new content item.</p>
     */
    onNewItemIntent: function()
    {
        this.logInfo("onNewItemIntent");
    },

    /**
     * @description <p>Handles storing a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting a users intent to have his editing progress saved.</p>
     */
    onStoreItemIntent: function()
    {
        this.logInfo("onStoreItemIntent");
    },

    /**
     * @description <p>Handles deleting a content item.</p>
     * <p>Mostly this method will be invoked from a view,
     * thereby reflecting a users intent of wanting to delete a content item.</p>
     */
    onDeleteItemIntent: function()
    {
        this.logInfo("onDeleteItemIntent");
    }
});