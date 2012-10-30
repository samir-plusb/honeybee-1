midas.list.ListItemModel = midas.core.BaseObject.extend({

    log_prefix: "ListItemModel",

    data: null,

    display_data: null,

    css_classes: null,

    ticket: null,

    selected: null,

    init: function(item_data)
    {
        this.parent();

        this.data = item_data.data;
        this.ticket = item_data.ticket;
        this.display_data = item_data.display_data;
        this.css_classes = item_data.css_classes ? item_data.css_classes.join(" ") : "";
        this.selected = ko.observable(false);
    }
});