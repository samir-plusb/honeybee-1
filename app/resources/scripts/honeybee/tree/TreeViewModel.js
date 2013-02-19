honeybee.tree.TreeViewModel = honeybee.core.BaseObject.extend({

    log_prefix: "TreeViewModel",

    list_items: null,

    init: function(controller, tree_data)
    {
        this.parent();

        this.list_items = ko.observableArray();
        var that = this;
        this.initItems(tree_data);
    },

    initItems: function(items)
    {
        /* This is copied from ListViewModel for reference
        for (var i = 0; i < items.length; i++)
        {
            this.addItem(
                new honeybee.list.ListItemModel(items[i])
            );
        }
        */
    }
});

