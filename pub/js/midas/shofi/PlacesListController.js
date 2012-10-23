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
    },

    createResolveConflictBatch: function(items, category)
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

    createResolveConflictBatchPayload: function(data_model, category)
    {
        var url = this.options.category_batch.post_url.replace('{TICKET}', data_model.ticket.id);
        var detailItem = { category: category.id };
        return this.ajaxCurry(url, { detailItem: detailItem }, 'post');
    },

    markDeduplicated: function(is_batch, data_container)
    {
        if (! is_batch)
        {
            alert("Orte zu deduplizieren wird nur über die Stapelverarbeitung unterstützt.");
            return;
        }
        
        var url = this.options.dedup_url;
        var batch = new midas.list.ActionBatch();
        var items = this.getSelectedItems();
        for(i = 0; i < items.length; i++)
        {
            batch.addAction(new midas.list.Action(
                midas.core.Request.curry(url, { item_id: items[i].data.identifier}, 'post')
            ));
        }
        batch.run();
    }
});
