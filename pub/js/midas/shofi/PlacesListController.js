midas.shofi.PlacesListController = midas.list.ListController.extend({

    log_prefix: "PlacesListController",

    category_dialog: null,

    init: function(options)
    {
        this.parent(options);
        this.category_dialog = new midas.shofi.SelectCategoryDialog(
            this.options.category_batch.autocomplete_url
        );
    },

    assignCategory: function(is_batch, data_container)
    {
        var that = this;
        var onCategorySelected = function(category)
        {
            that.category_dialog.hide().reset().removeListener('category::selected', onCategorySelected);
            var items = (true === is_batch) ? that.getSelectedItems() : [ item ];
            that.createCategoryBatch(items, category).run();
        };
        this.category_dialog.on('category::selected', onCategorySelected).show();
    },

    createCategoryBatch: function(items, category)
    {
        var batch = new midas.list.ActionBatch();
        for (var i = 0; i < items.length; i++)
        {
            batch.addAction(new midas.list.Action(
                this.createCategoryBatchPayload(items[i], category)
            ));
        }
        return batch;
    },

    createCategoryBatchPayload: function(data_model, category)
    {
        var url = this.options.category_batch.post_url.replace('{TICKET}', data_model.ticket.id);
        var detailItem = { category: category.id };
        return this.ajaxCurry(url, { detailItem: detailItem }, 'post');
    }
});
