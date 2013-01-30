honeybee.list.ListController = honeybee.core.BaseObject.extend({

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
        this.workflow_handler = new honeybee.list.WorkflowHandler(
            this.options.workflow_urls || {}
        );
        this.viewmodel = new honeybee.list.ListViewModel(
            this, 
            JSON.parse($('input.list-data').val())
        );
        ko.applyBindings(this.viewmodel);
        this.search_widget = honeybee.widgets.Widget.factory('.search-widget', 'SearchWidget');
        this.confirm_dialog = new honeybee.list.ListController.ConfirmDialog('.dialog-confirm');
    },

    loadData: function(data)
    {
        this.viewmodel.list_items.removeAll();
        this.viewmodel.initItems(data);
    },

    proceed: function(is_batch, data, gate, confirm_text)
    {
        var that = this;
        confirm_text = (undefined === confirm_text) ? false : confirm_text;
        var proceed = function()
        {
            that.createProceedBatch(
                (true === is_batch) ? that.getSelectedItems() : [ data ], 
                gate
            ).run();
        };

        if (confirm_text)
        {
            this.confirm_dialog.confirm_prompt.find('.prompt-text').html(confirm_text);
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
        var batch = new honeybee.list.ActionBatch();
        var has_errors = false;
        for (var i = 0; i < items.length; i++)
        {
            var item = items[i];
            
            batch.addAction(new honeybee.list.Action(
                this.workflow_handler.proceed(item, gate)
            ));
        }

        batch.on('success', function()
        {
            console.log("yay batch item succeeded.");
        }).on('error', function(err)
        {
            has_errors = true;
            console.log("noes batch item failed.", err);
        }).on('complete', function()
        {
            if (has_errors)
            {
                // do something to communicate the errors.
                alert("Weird stuff is happening causing errors!");
            }

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

        this.workflow_handler.run(data);
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

    /**
     * @deprecated Use honeybee.core.Request.curry instead.
     */
    ajaxCurry: function(url, data, method, type)
    {
        return honeybee.core.Request.curry(url, data, method, type);
    }
});

honeybee.list.ListController.create = function(element, namespace)
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

honeybee.list.ListController.ConfirmDialog = honeybee.core.BaseObject.extend({

    log_prefix: 'ConfirmDialog',

    confirm_prompt: null,

    init: function(element)
    {
        this.parent();
        var that = this;
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
