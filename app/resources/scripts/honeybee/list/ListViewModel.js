honeybee.list.ListViewModel = honeybee.core.BaseObject.extend({

    log_prefix: "ListViewModel",

    ctrl: null,

    workflow_handler: null,

    selected_items: null,

    all_selected: null,

    list_items: null,

    dont_toggle: null,

    has_selection: null,

    has_filter: null,

    has_search: null,

    item_count: null,

    supported_batch_actions: null,

    actionCountMap: null,

    alerts: null,

    init: function(controller, list_data)
    {
        this.parent();

        this.ctrl = controller;
        var that = this;
        this.workflow_handler = this.ctrl.workflow_handler;
        this.dont_toggle = false; // helper to prevent toggle recursion.
        this.selected_items = ko.observableArray([]);
        this.list_items = ko.observableArray();
        this.alerts = ko.observableArray([]);
        this.all_selected = ko.observable(false);
        this.all_selected.subscribe(this.toggleAllItemsSelection.bind(this));
        this.actionCountMap = {};

        this.select_all_toggle = $('.select-all-toggle');

        var that = this;
        this.supported_batch_actions = {};
        this.ctrl.element.find('.batch_actions .honeybee-action').each(function(idx, dom_element)
        {
            var action_trigger = $(dom_element);
            var class_name, matches, i, classes = action_trigger.attr('class').split(/\s+/);

            for (i = 0; i < classes.length; i++)
            {
                class_name = classes[i];
                if ((matches = class_name.match(/^honeybee-action-([\w_-]+)/)))
                {
                    that.supported_batch_actions[matches[1]] = action_trigger.parent('li');
                }
            }
        });

        var incrementActionCount = function (action_name)
        {
            if (typeof that.actionCountMap[action_name] === 'undefined')
            {
                that.actionCountMap[action_name] = 1;
            }
            else
            {
                that.actionCountMap[action_name]++;
            }
        };

        this.has_selection = ko.computed(function()
        {
            that.actionCountMap = {};

            var enabled_actions = [], selected_states = [],
                action, action_name, action_element, i, n;
            // make sure only items with the same workflow state are selected
            for (i = 0; i < that.selected_items().length; i++)
            {
                selected_item = that.selected_items()[i];
                var matches = selected_item.css_classes.match(/state\-(\w+)/);
                if (matches && selected_states.indexOf(matches[1]) == -1)
                {
                    selected_states.push(matches[1]);
                }
                if (i > 0 && selected_states.length > 1)
                {
                    return false;
                }
            }
            // collect the various actions supported by the current selection
            for (i = 0; i < that.selected_items().length; i++)
            {
                var action = selected_item.workflow.gates[n];
                for (n = 0; n < selected_item.workflow.gates.length; n++)
                {
                    action = selected_item.workflow.gates[n];
                    enabled_actions[action.name] = action.label;
                }
                if (true === selected_item.workflow.interactive)
                {
                    for (n = 0; n < selected_item.custom_actions.length; n++)
                    {
                        action = selected_item.custom_actions[n];
                        enabled_actions[action.name] = action.label;
                    }
                }
            }
            // enable/disable and translate the batchaction-picker's items
            for (action_name in that.supported_batch_actions)
            {
                var action_item = that.supported_batch_actions[action_name];
                var enabled_action = enabled_actions[action_name];
                if (enabled_action)
                {
                    action_item.removeClass('disabled');
                    action_item.find('.honeybee-action').text(enabled_action);
                }
                else
                {
                    action_item.addClass('disabled');
                }
            }

            return 0 < that.selected_items().length;
        });

        this.has_filter = ko.observable(false);
        this.has_search = ko.observable(false);

        this.initItems(list_data.listItems);
        this.initMetadata(list_data.metaData);
    },

    initItems: function(items)
    {
        for (var i = 0; i < items.length; i++)
        {
            this.addItem(
                new honeybee.list.ListItemModel(items[i])
            );
        }
    },

    initMetadata: function(metaData)
    {
        this.has_search(metaData.search ? true : false);
        this.has_filter(metaData.has_filter);
        this.item_count = ko.observable(metaData.item_count);
    },

    clearFilter: function() {
        honeybee.core.events.fireEvent('clearFilter');
    },

    addItem: function(item)
    {
        this.list_items.push(item);
        item.selected.subscribe(
            this.onItemSelectionToggled.bind(this, item)
        );
    },

    onItemSelectionToggled: function(item)
    {
        if (item.selected())
        {
            this.selected_items.push(item);
            this.ctrl.onItemSelected(item);
        }
        else
        {
            this.selected_items.remove(item);
            this.ctrl.onItemDeselected(item);
        }

        if (this.dont_toggle) return;
        this.dont_toggle = true;
        this.all_selected(this.list_items().length === this.selected_items().length);
        this.dont_toggle = false;
        this.updateCheckmark();
    },

    toggleAllItemsSelection: function(selected_state)
    {
        if (this.dont_toggle) return;

        this.dont_toggle = true;
        ko.utils.arrayForEach(this.list_items(), function(item) {
            item.selected(selected_state);
        }.bind(this));
        this.select_all_toggle.toggleClass('hb-icon-checkmark hb-icon-checkmark-2');
        this.dont_toggle = false;
    },

    selectAllToggle: function()
    {
        var all_selected = (this.list_items().length === this.selected_items().length);
        this.all_selected(!all_selected);
    },

    invertAllSelected: function()
    {
        ko.utils.arrayForEach(this.list_items(), function(item) {
            item.selected(!item.selected());
        }.bind(this));
        this.updateCheckmark();
    },

    updateCheckmark: function()
    {
        var num_selected = this.selected_items().length;
        var num_all = this.list_items().length;
        if (num_selected === num_all)
        {
            this.select_all_toggle.removeClass('hb-icon-checkmark-2').addClass('hb-icon-checkmark');
        }
        else
        {
            this.select_all_toggle.removeClass('hb-icon-checkmark').addClass('hb-icon-checkmark-2');
        }
    },

    addAlert: function(alert)
    {
        console.log(alert);
        var that = this;
        this.alerts.push(alert);
        setTimeout(function()
        {
            if (-1 !== that.alerts.indexOf(alert)) // if alert is still there.
            {
                that.removeAlert(alert);
            }
        }, 10000);
    },

    removeAlert: function(alert)
    {
        this.alerts.remove(alert);
    },

    showAlert: function(elem, idx, alert)
    {
        var that = this;
        if (elem.nodeType === 1)
        {
            $(elem).animate({
               opacity: 1
            }, { duration: 600, queue: false });
            $(elem).animate({
               'margin-top': '0px'
            }, { duration: 400, queue: false });
        }
    },

    hideAlert: function(elem, idx, alert)
    {
        if (elem.nodeType === 1)
        {
            $(elem).animate({
               opacity: 0
            }, { duration: 300, queue: false });
            $(elem).animate({
               'margin-top': '-55px'
            }, { duration: 600, queue: false, complete: function()
            {
                $(elem).remove();
            } });
        }
    }
});
