midas.items.edit.EditController = midas.core.BaseObject.extend({

    log_prefix: "EditController",

    view: null,

    init: function()
    {
        this.view = new midas.items.edit.EditView(
            this,
            $(document.body)[0], {
                'tabs_container': '.item-content'
            }
        );
    },

    onStoreItemIntent: function()
    {
        this.logInfo("onStoreItemIntent");
    },

    onDeleteItemIntent: function()
    {
        this.logInfo("onDeleteItemIntent");
    },

    onNewItemIntent: function()
    {
        this.logInfo("onNewItemIntent");
    },

    onListItemsIntent: function()
    {
        this.logInfo("onListItemsIntent");
    }
});