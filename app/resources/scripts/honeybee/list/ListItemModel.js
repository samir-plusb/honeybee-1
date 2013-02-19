honeybee.list.ListItemModel = honeybee.core.BaseObject.extend({

    log_prefix: "ListItemModel",

    data: null,

    display_data: null,

    css_classes: null,

    workflow: null,

    selected: null,

    init: function(item_data)
    {
        this.parent();

        this.data = item_data.data;
        this.workflow = item_data.workflow;
        this.display_data = item_data.display_data;
        this.css_classes = item_data.css_classes ? item_data.css_classes.join(" ") : "";
        this.selected = ko.observable(false);
    },

    dragStart: function(data, ev)
    {
        ev.originalEvent.dataTransfer.setData('text/plain', this.data.identifier);
        return true;
    }
});
