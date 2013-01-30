honeybee.widgets.SearchWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "SearchWidget",

    form: null,

    filter_dialog: null,

    // <knockout_props>
    labels: null,

    sort_direction: null,

    sort_field: null,

    search: null,

    has_search: null,

    has_filter_dialog: null,

    search_url: '',
    // </knockout_props>

    init: function(element, options)
    {
        this.parent(element, options);
        this.form = this.element.find('form').first();

        this.filter_dialog = $('.modal-search-filter').twodal({
            show: false,
            backdrop: true
        });

        this.filter_dialog.find('.widget').each(function(idx, widget_el)
        {
            var parts = $(widget_el).attr('class').split(' ');
            for (var i = 0; i < parts.length; i++)
            {
                css_class = parts[i];
                if (css_class.match(/^widget-/g))
                {
                    honeybee.widgets.Widget.factory(widget_el, css_class.replace('widget-', ''));
                }
            }
        });
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/SearchWidget.html';
    },

    initKnockoutProperties: function()
    {
        this.labels = {
            search: ko.observable(this.options.labels.search),
            placeholder: ko.observable(this.options.labels.placeholder),
            filter: ko.observable(this.options.labels.filter)
        };
        this.search = ko.observable(this.options.search || '');
        this.sort_direction = ko.observable(this.options.sort_direction);
        this.sort_field = ko.observable(this.options.sort_field);
        this.search_url = ko.observable(this.options.search_url);
        this.has_filter_dialog = ko.observable(this.filter_dialog.length === 1);
        var that = this;
        this.has_search = ko.computed(function()
        {
            return that.search().length > 0;
        });
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    doSearch: function()
    {
        return true;
    },

    showFilterDialog: function()
    {
        this.filter_dialog.twodal('show');
        return false;
    },

    resetSearchPhrase: function()
    {
        this.search('');
        this.form.find('.search-query').focus();
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.SearchWidget.DEFAULT_OPTIONS = {
    autobind: true,
    search_url: window.location.href,
    filter_url: '',
    search: '',
    sort_direction: 'desc',
    sort_field: 'identifier',
    labels: {
        search: 'Suche',
        placeholder: 'Suche',
        filter: '(Erweiterte Suche)'
    }
};
