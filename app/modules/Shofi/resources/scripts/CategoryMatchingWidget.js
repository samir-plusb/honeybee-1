
midas.shofi.CategoryMatchingWidget = midas.core.BaseObject.extend({

    element: null,

    rows: null,

    autocomplete_options: null,

    init: function(element)
    {
        this.element = element;
        var options_string = this.element.attr('data-category-options');
        var options = JSON.parse(options_string);

        this.rows = ko.observableArray([]);
        this.autocomplete_options = options.autocomplete;
        
        var that = this;
        $.each(options.mappings, function(ext_category, categories)
        {
            that.rows.push(
                new midas.shofi.CategoryMatchingWidget.Row(
                    element,
                    ext_category, 
                    categories, 
                    that.autocomplete_options
                )
            );
        });

        ko.applyBindings(this, this.element[0]);

        for(var i = 0; i < this.rows().length; i++)
        {
            this.rows()[i].activate();
        }
    }

});

midas.shofi.CategoryMatchingWidget.Row = midas.core.BaseObject.extend({

    element: null,

    ext_category: null,

    ext_category_display: null,

    categories: null, 

    selector: null,

    busy: null,

    init: function(element, ext_category, categories, options)
    {
        this.element = element;
        categories = categories || [];

        options.tags = categories;
        options.fieldname = ext_category.toLowerCase().replace(/[\/\s:]/g, '-').replace('&', 'and');
        options.tpl = midas.widgets.TagsList.TPL.INLINE;
        this.autocomplete_options = $.extend({}, midas.widgets.TagsList.DEFAULT_OPTIONS, options);

        this.ext_category = ko.observable(ext_category);
        this.busy = ko.observable(false);
        this.categories = ko.observableArray(categories || []);
        
        var that = this;        
        this.selector = ko.computed(function()
        {
            return options.fieldname + '-matches-row';
        });
        this.ext_category_display = ko.computed(function()
        {
            var parts = ext_category.split(':');
            return parts[0] + ':<b>' + parts[1] + '</b>';
        });
    },

    activate: function()
    {
        var that = this;
        var widget_el = $('.'+this.selector()).first();
        var widget = new midas.widgets.TagsList(
            widget_el,
            this.autocomplete_options,
            function()
            {
                widget_el.find('.tagslist-tag').each(function(idx, tag_item)
                {
                    if (0 < idx)
                    {
                        $(tag_item).removeClass('btn-info');
                    }
                });
            }
        );
        widget.on('tagschanged', function(fieldname, mappings)
        {
            var category_ids = [];
            for(var i = 0; i < mappings.length; i++)
            {
                category_ids.push(mappings[i].value);
            }
            var data = {
                category: that.ext_category(),
                mappings: category_ids
            };
            widget.busyStart("Speichere Änderungen ...");
            var onRequestComplete = function()
            {
                widget.busyEnd();
            };
            midas.core.Request.curry(location.href, data, 'post')(onRequestComplete, onRequestComplete);
            widget_el.find('.tagslist-tag').each(function(idx, tag_item)
            {
                if (0 < idx)
                {
                    $(tag_item).removeClass('btn-info');
                }
                else
                {
                    $(tag_item).addClass('btn-info');
                }
            });
        });
    }
});
