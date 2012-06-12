midas.shofi.CategoriesListController = midas.list.ListController.extend({

    log_prefix: "CategoriesListController",

    vertical_dialog: null,

    init: function(options)
    {
        this.parent(options);
        this.vertical_dialog = new midas.shofi.SelectVerticalDialog(
            $('.modal-vertical-select').first(),
            this.options.vertical_batch.autocomplete_url
        );
    },

    assignVertical: function(is_batch, data_container)
    {
        var that = this;
        var onVerticalSelected = function(vertical)
        {
            that.vertical_dialog.hide().reset().removeListener('vertical::selected', onVerticalSelected);
            var items = (true === is_batch) ? that.getSelectedItems() : [ data_container ];
            that.createVerticalBatch(items, vertical).run();
        };
        this.vertical_dialog.on('vertical::selected', onVerticalSelected).show();
    },

    createVerticalBatch: function(items, vertical)
    {
        var batch = new midas.list.ActionBatch();
        for (var i = 0; i < items.length; i++)
        {
            batch.addAction(new midas.list.Action(
                this.createVerticalBatchPayload(items[i], vertical)
            ));
        }
        return batch;
    },

    createVerticalBatchPayload: function(data_model, vertical)
    {
        var url = this.options.vertical_batch.post_url.replace('{TICKET}', data_model.ticket.id);
        var detailItem = { vertical: vertical.id };
        return this.ajaxCurry(url, { detailItem: detailItem }, 'post');
    }
});
