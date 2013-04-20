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

    init: function(controller, list_data)
    {
        this.parent();

        this.ctrl = controller;
        var that = this;
        this.workflow_handler = this.ctrl.workflow_handler;
        this.dont_toggle = false; // helper to prevent toggle recursion.
        this.selected_items = ko.observableArray([]);
        this.list_items = ko.observableArray();
        this.all_selected = ko.observable(false);
        this.all_selected.subscribe(this.toggleAllItemsSelection.bind(this));
        this.actionCountMap = {};

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

        this.has_selection = ko.computed(function()
        {
            that.actionCountMap = {};
            
            var enabled_actions_count = 0, 
                action, action_name, action_element, i, n;

            function incrementActionCount(action_name)
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

            for (i = 0; i < that.selected_items().length; i++)
            {
                selected_item = that.selected_items()[i];
                
                for (n = 0; n < selected_item.workflow.gates.length; n++)
                {
                    incrementActionCount(selected_item.workflow.gates[n].name);
                }
                for (n = 0; n < selected_item.custom_actions.length; n++)
                {
                    incrementActionCount(selected_item.custom_actions[n].name);
                }
            }

            for (action_name in that.supported_batch_actions)
            {
                action_element = that.supported_batch_actions[action_name];
                if (that.actionCountMap[action_name] === that.selected_items().length)
                {
                    action_element.removeClass('disabled');
                    enabled_actions_count++;
                }
                else
                {
                    action_element.addClass('disabled');
                }
            }

            return 0 < that.selected_items().length && 0 < enabled_actions_count;
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
    },

    toggleAllItemsSelection: function(selected_state)
    {
        if (this.dont_toggle) return;

        this.dont_toggle = true;
        ko.utils.arrayForEach(this.list_items(), function(item) {
            item.selected(selected_state);
        }.bind(this));
        this.dont_toggle = false;
    }
});
