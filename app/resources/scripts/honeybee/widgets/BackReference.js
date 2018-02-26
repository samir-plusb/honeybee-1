honeybee.widgets.BackReference = honeybee.widgets.Widget.extend({

    log_prefix: "BackReference",
    fieldname: null,
    tags: null,
    loading_class: null,

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
    },

    getTemplate: function()
    {
        return 'static/widgets/BackReference.html';
    },

    initGui: function()
    {
        this.parent();
        var self = this;
        $.getJSON(this.options.auto_complete.uri, function (a) {
            $.each(a.listItems,function(idx,item){
                 self.tags.push({
                     css: ko.observable(item.css_classes.join(' ')),
                     label: ko.observable(item.data[self.options.auto_complete.display_field]),
                     id: ko.observable(item.data[self.options.auto_complete.identity_field])
                 });
            });

        })
    },

    initKnockoutProperties: function()
    {
        this.tags = ko.observableArray([]);
        this.loading_class = ko.observable('loading_ajax');
    }

});

// #####################
// #     constants     #
// #####################
honeybee.widgets.BackReference.DEFAULT_OPTIONS = {
    autobind: true
};
