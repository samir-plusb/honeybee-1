midas.list.ListViewModel = midas.core.BaseObject.extend({

    log_prefix: "ListViewModel",

    ctrl: null,

    workflow_handler: null,

    selected_items: null,

    all_selected: null,

    list_items: null,

    dont_toggle: null,

    has_selection: null,

    init: function(controller, list_data)
    {
        this.parent();

        this.ctrl = controller;
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
        this.initItems(list_data);
    },

    initItems: function(items)
    {
        for (var i = 0; i < items.length; i++)
        {
            this.addItem(
                new midas.list.ListItemModel(items[i])
            );
        }
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
        }
        else
        {
            this.selected_items.remove(item);
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