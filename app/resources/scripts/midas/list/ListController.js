midas.list.ListController = midas.core.BaseObject.extend({

    log_prefix: "ListController",

    viewmodel: null,

    workflow_handler: null,

    search_widget: null,

    confirm_dialog: null,

    options: null,

    init: function(options)
    {
        this.parent();
        this.options = options;
    },

    attach: function()
    {
        this.workflow_handler = new midas.list.WorkflowHandler(
            this.options.workflow_urls || {}
        );
        this.viewmodel = new midas.list.ListViewModel(
            this, 
            JSON.parse($('input.list-data').val())
        );
        ko.applyBindings(this.viewmodel);
        this.search_widget = midas.widgets.Widget.factory('.search-widget', 'SearchWidget');
        this.confirm_dialog = new midas.list.ListController.ConfirmDialog('.dialog-confirm');
    },

    proceed: function(is_batch, data, gate, confirm)
    {
        var that = this;
        confirm = (undefined === confirm) ? true : confirm;
        var proceed = function()
        {
            that.createProceedBatch(
                (true === is_batch) ? that.getSelectedItems() : [ data ], 
                gate
            ).run();
        };

        if (confirm)
        {
            this.confirm_dialog.show(function()
            {
                that.confirm_dialog.hide();
                proceed();
            });
        }
        else
        {
            proceed();
        }
    },

    createProceedBatch: function(items, gate)
    {
        var batch = new midas.list.ActionBatch();
        for (var i = 0; i < items.length; i++)
        {
            var item = items[i];
            batch.addAction(new midas.list.Action(
                this.workflow_handler.proceed(item.ticket, gate)
            ));
        }
        batch.on('complete', function()
        {
            window.location.href = window.location.href;
        });
        return batch;
    },

    run: function(is_batch, data)
    {
        if (is_batch)
        {
            // no batch support for editing/plugin execution
            return;
        }

        var edit_link = this.options.workflow_urls.edit + '?id='+data.data.identifier;
        window.location.href = edit_link;
        /* @todo integrate/consider ticket data
        var checkout = this.workflow_handler.checkout(data);
        var that = this;
        checkout(function(ticket)
        {
            that.workflow_handler.run(ticket);
        }, function()
        {
            alert("Datensatz konnte nicht geöffnet werden, da er bereits bearbeitet wird.");
        });
*/
    },

    releaseTicket: function(data)
    {
        var release = this.workflow_handler.release(data.ticket);
        var that = this;
        release(function()
        {
            window.location.href = window.location.href;
        }, function()
        {
            alert("Ticket konnte nicht freigegeben werden.");
        });
    },

    getSelectedItems: function()
    {
        return this.viewmodel.selected_items();
    },

    deleteItem: function(is_batch, data_container)
    {
        var that = this;

        this.confirm_dialog.show(function()
        {
            that.confirm_dialog.hide();
            that.createDeleteBatch(
                (true === is_batch) ? that.getSelectedItems() : [ data_container ]
            ).run();
        });
    },

    createDeleteBatch: function(items)
    {
        var batch = new midas.list.ActionBatch();

        for (var i = 0; i < items.length; i++)
        {
            var item = items[i];
            batch.addAction(new midas.list.Action(
                midas.core.Request.curry(
                    this.options.workflow_urls.delete, 
                    { id: item.data.identifier }, 
                    'post'
                )
            ));
        }

        batch.on('complete', function()
        {
            window.location.href = window.location.href;
        });

        return batch;
    },

    /**
     * @deprecated Use midas.core.Request.curry instead.
     */
    ajaxCurry: function(url, data, method, type)
    {
        return midas.core.Request.curry(url, data, method, type);
    }
});

midas.list.ListController.create = function(element, namespace)
{
    element = $(element);
    if (0 === element.length)
    {
        throw "Unable to find element to create controller from. Looked for: " + element;
    }
    var controller_class = element.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "Unable to resolve controller implementor: " + controller_class;
    }
    var options = element.attr('data-controller-options') || "{}";
    var controller = new namespace[controller_class](JSON.parse(options));
    return controller;
};

midas.list.ListController.ConfirmDialog = midas.core.BaseObject.extend({

    log_prefix: 'ConfirmDialog',

    confirm_prompt: null,

    init: function(element, categories_uri)
    {
        this.parent();
        var that = this;
        this.categories_uri = categories_uri;
        this.confirm_prompt = $(element).twodal({
            show: false,
            backdrop: true,
            events: {
                confirm: this.onConfirmed.bind(this)
            }
        });
    },

    show: function(confirm_callback)
    {
        this.removeAllListeners('confirmed');
        this.on('confirmed', confirm_callback);
        this.confirm_prompt.twodal('show');
        return this;
    },

    hide: function()
    {
        this.confirm_prompt.twodal('hide');
        return this;
    },

    onConfirmed: function()
    {
        this.fire('confirmed');
    }
});
