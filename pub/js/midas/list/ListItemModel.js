midas.list.ListItemModel = midas.core.BaseObject.extend({

    log_prefix: "ListItemModel",

    data: null,

    display_data: null,

    ticket: null,

    selected: null,

    init: function(item_data)
    {
        this.parent();

        this.data = item_data.data;
        this.ticket = item_data.ticket;
        this.display_data = item_data.display_data;
        this.selected = ko.observable(false);
    }
});
