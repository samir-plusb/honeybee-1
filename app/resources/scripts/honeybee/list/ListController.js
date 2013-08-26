honeybee.list.ListController = honeybee.core.BaseObject.extend({

    log_prefix: "ListController",

    type_key: 'list_controller',

    viewmodel: null,

    workflow_handler: null,

    search_widget: null,

    confirm_dialog: null,

    element: null,

    options: null,

    init: function(options)
    {
        this.parent();

        this.options = $.extend(true, {}, options);
    },

    attach: function()
    {
        this.bindSidebarEvents();

        this.element = $('.container-list-data');

        this.workflow_handler = new honeybee.list.WorkflowHandler(
            this.options.workflow_urls || {}
        );
        this.viewmodel = new honeybee.list.ListViewModel(
            this,
            JSON.parse($('input.list-data').val())
        );

        ko.applyBindings(this.viewmodel, this.element[0]);

        this.search_widget = honeybee.widgets.Widget.factory('.search-widget', 'SearchWidget');
        this.confirm_dialog = new honeybee.list.ListController.ConfirmDialog('.dialog-confirm');
        this.reference_dialog = $('.assign-reference-modal');

        var that = this;
        honeybee.core.events.on('filterBy', function(data)
        {
            var filter = {};
            if (typeof data.field !== "undefined" && typeof data.value !== "undefined")
            {
                filter[data.field] = data.value;
                that.reloadList({
                    filter: filter
                });
            }
        });

        var messageEventHandler = function(event)
        {
            if(0 === that.options.event_origin.indexOf(event.origin))
            {
                var msg_data = JSON.parse(event.data);
                if (msg_data.source_type === that.type_key)
                {
                    // ignore our own messages.
                    return;
                }
                var cur_item, i;
                for (i = 0; i < that.viewmodel.list_items().length; i++)
                {
                    cur_item = that.viewmodel.list_items()[i];
                    cur_item.selected(
                        (-1 !== msg_data.selected_doc_ids.indexOf(cur_item.data.identifier))
                    );
                }
            }
        }

        window.addEventListener('message', messageEventHandler,false);
        honeybee.core.events.on('clearFilter', function() { that.reloadList({}); });
        window.top.postMessage(
            JSON.stringify({
                'event_type': 'list-loaded',
                'source_type': this.type_key,
                'reference_field': this.options.reference_field
            }),
            this.options.event_origin
        );
    },

    reloadList: function(parameters)
    {
        var that = this;
        var href;
        parameters = parameters || {};
        if (parameters)
        {
            href = document.location.href.split('?')[0];
            if ($.url().param('only_assigned_docs'))
            {
                parameters.only_assigned_docs = true;
            }
            history.pushState(parameters, 'list_reload', href + '?' + $.param(parameters));
        }
        else
        {
            href = document.location.href;
        }

        $.getJSON(href, parameters, function(data, status, jqXHR)
        {
            that.loadData(data);
        });
    },

    loadData: function(data)
    {
        this.viewmodel.list_items.removeAll();
        this.viewmodel.initItems(data.listItems);
        this.viewmodel.initMetadata(data.metaData);

        window.top.postMessage(
            JSON.stringify({
                'event_type': 'list-loaded',
                'source_type': this.type_key,
                'reference_field': this.options.reference_field
            }),
            this.options.event_origin
        );
    },

    getSelectedItems: function()
    {
        return this.viewmodel.selected_items();
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

    assignReference: function(is_batch, item, reference_field)
    {
        var that = this;
        var batch_options = this.options.reference_batches[reference_field];
        var labels = batch_options.widget_options.texts;

        var reference_dialog = this.reference_dialog.clone();
        reference_dialog.find('.modal-header .header').text(labels.field_label);
        reference_dialog.find('.setting-override-existing .setting-label').text(labels.override_references)
            .attr('for', reference_field + '_override_selector');
        reference_dialog.find('.setting-append-to-existing .setting-label').text(labels.append_references)
            .attr('for', reference_field + '_append_to_selector');
        reference_dialog.find('.setting-append-to-existing .setting-value').attr('checked', 'checked')
            .attr('id', reference_field + '_append_to_selector');
        reference_dialog.find('.setting-override-existing .setting-value')
            .attr('id', reference_field + '_override_selector');

        var assign_trigger = reference_dialog.find('.select-reference');
        assign_trigger.text(labels.assign_references);
        var close_trigger = reference_dialog.find('.modal-header .close-dialog');

        var widget_container = reference_dialog.find('.widget-tags-list');
        var widget_options = batch_options.widget_options;
        var reference_widget = new honeybee.widgets.Reference(widget_container, widget_options);

        if (widget_options.max === 1)
        {
            reference_dialog.find('.settings-reference-batch').css('visibility', 'hidden');
        }
        
        assign_trigger.click(function()
        {
            reference_dialog.modal('hide');

            var items = (true === is_batch) ? that.getSelectedItems() : [ item ];

            if (0 < reference_widget.tags().length)
            {
                var references = reference_widget.tags();
                var append_references = reference_dialog.find(
                    '.setting-append-to-existing .setting-value'
                ).is(':checked');

                append_references = append_references && (1 !== reference_widget.options.max);

                that.createAssignReferenceBatch(items, {
                    reference_field: reference_field,
                    references: references
                }, append_references).on('complete', function()
                {
                    that.reloadList();
                }).run();
            }
        });

        reference_dialog.modal({'show': true, 'backdrop': 'static'});
        close_trigger.click(function()
        {
            reference_dialog.modal('hide');
        });
        reference_dialog.on('hidden', function()
        {
            reference_dialog.remove();
        });
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
            // console.log("yay batch item succeeded.");
        }).on('error', function(err)
        {
            has_errors = true;
            // console.log("noes batch item failed.", err);
        }).on('complete', function()
        {
            if (has_errors)
            {
                // do something to communicate the errors.
                alert("Weird stuff is happening causing errors!");
            }
            // reload list after batch has finished
            window.location.href = window.location.href;
        });

        return batch;
    },

    createAssignReferenceBatch: function(items, ref_data, append_references)
    {
        var batch_options = this.options.reference_batches[ref_data.reference_field];

        if (! batch_options)
        {
            return;
        }

        var batch = new honeybee.list.ActionBatch();
        var that = this, i, n, reference, document_data, update_url;

        for (i = 0; i < items.length; i++)
        {
            if (! items[i].workflow.interactive)
            {
                // items that are non-interactive may not be batch assigned references
                // and therefore should not land here in the first place. let them be and continue on.
                continue;
            }

            document_data = {};
            document_data[this.options.module] = { identifier: items[i].data.identifier };

            var field_references = (true === append_references) ? items[i].data[ref_data.reference_field] : [];

            for (n = 0; n < ref_data.references.length; n++)
            {
                reference = ref_data.references[n];
                field_references.push({ 'id': reference.id, 'module': reference.module_prefix });
            }

            document_data[this.options.module][ref_data.reference_field] = field_references;

            update_url = batch_options.update_url.replace('{ID}', items[i].data.identifier);

            batch.addAction(new honeybee.list.Action(
                honeybee.core.Request.curry(update_url, document_data, 'post')
            ));
        }

        return batch;
    },

    bindSidebarEvents: function()
    {
        var that = this;

        honeybee.core.events.on('itemDroppedOnItem', function(data)
        {
            var items = [];

            ko.utils.arrayForEach(that.viewmodel.list_items(), function(element)
            {
                if (element.data.identifier === data.sourceId)
                {
                    items.push(element);
                }
            });

            ko.utils.arrayForEach(that.viewmodel.selected_items(), function(element)
            {
                if (element.data.identifier !== data.sourceId)
                {
                    items.push(element);
                }
            });

            that.createAssignReferenceBatch(items, {
                reference_field: data.reference_field,
                references: [ { id: data.id, module_prefix: data.module } ]
            }, false).on('complete', function()
            {
                that.reloadList();
                that.viewmodel.selected_items.removeAll();
            }).run();
        });

        this.bindSidebarTreeClickEvents();
    },

    bindSidebarTreeClickEvents: function()
    {
        var that = this;

        var referenceSelectHandler = function(target_module)
        {
            honeybee.core.events.once('reference::targetSelected', function(data)
            {
                if (! that.options.reference_batches[data.reference_field])
                {
                    return;
                }

                if (0 === that.getSelectedItems().length)
                {
                    return;
                }

                var batch_items = that.getSelectedItems();

                that.createAssignReferenceBatch(batch_items, {
                    reference_field: data.reference_field,
                    references: [ { id: data.id, module_prefix: data.module } ]
                }).on('complete', function()
                {
                    honeybee.core.events.fireEvent('reference::cancelTargetSelection', { module: data.module });
                    that.reloadList();
                    that.viewmodel.selected_items.removeAll();
                }).run();
            });

            honeybee.core.events.fireEvent('reference::startTargetSelection', { module: target_module });
        };

        $('.sidebar-tree-targets .tree-target').each(function(idx, element)
        {
            var target_container = $(element);
            var abort_trigger = target_container.find('.reference-abort');
            var assign_trigger = target_container.find('.reference-assign');

            honeybee.core.events.on('reference::cancelTargetSelection', function()
            {
                honeybee.core.events.off('reference::targetSelected', referenceSelectHandler);

                abort_trigger.hide();
                assign_trigger.show();
            });

            assign_trigger.click(function()
            {
                $('.edge-handles .expand').click(); // TODO: make this an event for the sidebar controller?
                if (! assign_trigger.hasClass('disabled'))
                {
                    referenceSelectHandler(assign_trigger.data('module'));

                    assign_trigger.hide();
                    abort_trigger.show();
                }
            });

            abort_trigger.click(function()
            {
                honeybee.core.events.fireEvent('reference::cancelTargetSelection', {
                    module: abort_trigger.data('module')
                });
            });
        });
    },

    updateSidebarClickTriggers: function()
    {
        var that = this;
        $('.sidebar-tree-targets .tree-target').each(function(idx, element)
        {
            var target_container = $(element);
            var assign_trigger = target_container.find('.reference-assign');
            var action = assign_trigger.data('action');
            var itemCount = that.getSelectedItems().length;

            if (! that.viewmodel.actionCountMap[action] || itemCount !== that.viewmodel.actionCountMap[action])
            {
                honeybee.core.events.fireEvent('reference::cancelTargetSelection', {
                    module: assign_trigger.data('module')
                });
                assign_trigger.addClass('disabled');
            }
            else
            {
                assign_trigger.removeClass('disabled');
            }
        });
    },

    /**
     * @deprecated Use honeybee.core.Request.curry instead.
     */
    ajaxCurry: function(url, data, method, type)
    {
        return honeybee.core.Request.curry(url, data, method, type);
    },

    onItemSelected: function(item)
    {
        if (true === this.options.select_only_mode)
        {
            var msg_payload = {
                event_type: 'item-added',
                source_type: this.type_key,
                reference_field: this.options.reference_field,
                reference_module: this.options.reference_module,
                item: {
                    id: item.data[this.options.reference_settings.identity_field],
                    text: item.data[this.options.reference_settings.display_field],
                    module: this.options.module
                }
            };

            window.top.postMessage(JSON.stringify(msg_payload), this.options.event_origin);
        }

        this.updateSidebarClickTriggers();
    },

    onItemDeselected: function(item)
    {
        if (true === this.options.select_only_mode)
        {
            var msg_payload = {
                event_type: 'item-removed',
                source_type: this.type_key,
                reference_field: this.options.reference_field,
                reference_module: this.options.reference_module,
                item: {
                    id: item.data[this.options.reference_settings.identity_field],
                    text: item.data[this.options.reference_settings.display_field],
                    module: this.options.module
                }
            };

            window.top.postMessage(JSON.stringify(msg_payload), this.options.event_origin);
        }

        this.updateSidebarClickTriggers();
    }
});

honeybee.list.ListController.create = function(element, namespace)
{
    element = $(element);

    if (0 === element.length)
    {
        throw "[ListController] Unable to find element to create controller from. Looked for: " + element;
    }
    var controller_class = element.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "[ListController] Unable to resolve controller implementor: " + controller_class;
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
