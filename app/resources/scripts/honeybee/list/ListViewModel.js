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

    init: function(controller, list_data)
    {
        this.parent();

        this.ctrl = controller;
        var that = this;
        this.workflow_handler = this.ctrl.workflow_handler;
        this.dont_toggle = false; // helper to prevent toggle recursion.
        this.selected_items = ko.observableArray();
        
        this.list_items = ko.observableArray();
        this.all_selected = ko.observable(false);
        this.all_selected.subscribe(this.toggleAllItemsSelection.bind(this));
        var that = this;
        this.has_selection = ko.computed(function()
        {
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
