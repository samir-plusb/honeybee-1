honeybee.widgets.AutoComplete = honeybee.widgets.Widget.extend({
    
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "AutoComplete",

    currently_valid: null,

    // <knockout_props>
    fieldname: null,

    current_entry: null,

    current_val: null,

    has_focus: null,

    is_valid: null,
    // </knockout_props>

    init: function(element, options)
    {
        this.parent(element, options);
        this.currently_valid = {};
        this.initTypeahead();
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return honeybee.widgets.AutoComplete.TPL;
    },

    initKnockoutProperties: function()
    {
        var that = this;
        this.fieldname = ko.observable(this.options.fieldname);
        this.current_entry = ko.observable('');
        this.current_val = ko.observable('');
        this.has_focus = ko.observable(false);
        this.is_valid = ko.observable(false);
        this.current_entry.subscribe(function(new_value)
        {
            var prop = that.options.autocomplete_prop;
            var value_prop = that.options.autocomplete_value_prop || prop;
            var is_mapped = prop !== value_prop;
            if (is_mapped && that.validate())
            {
                that.current_val(
                    that.currently_valid[that.current_entry()]
                );
            }
            else if (! is_mapped)
            {
                that.current_val(that.current_entry());
            }
        });
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    validate: function()
    {
        var entry = this.current_entry();
        this.is_valid(('undefined' !== typeof this.currently_valid[entry]));
        return this.is_valid();
    },

    // #########################
    // #     working funcs     #
    // #########################
    initTypeahead: function()
    {
        var that = this;
        this.element.find('.autocomp-input').typeahead({
            property: this.options.autocomplete_prop,
            source: this.fetchTypeAheadData.bind(this),
            items: this.options.autocomplete_limit,
            onselect: function(val)
            {
                var prop = that.options.autocomplete_prop;
                var value_prop = that.options.autocomplete_value_prop || prop;
                var is_mapped = prop !== value_prop;
                that.current_entry( // apply the selected autocomplete value
                    val[that.options.autocomplete_prop]
                );
                if (that.is_valid() && is_mapped)
                {
                    that.current_val(val[value_prop]);
                }
            }
        });
    },

    fetchTypeAheadData: function(typeahead, phrase)
    {
        var that = this;
        if (1 >= phrase.length)
        {
            typeahead.process([]);
            that.currently_valid = {};
            return;
        }
        var req = honeybee.core.Request.curry(that.options.autocomplete_uri.replace('{PHRASE}', phrase));
        req(function(resp)
        {
            var prop = that.options.autocomplete_prop;
            var value_prop = that.options.autocomplete_value_prop || prop;
            var data = resp.data;
            that.currently_valid = {};
            for (var i = 0; i < data.length; i++)
            {
                that.currently_valid[data[i][prop]] = data[i][value_prop];
            }
            typeahead.process(data);
        });
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.AutoComplete.DEFAULT_OPTIONS = {
    autobind: true,
    autocomplete_uri: null,
    autocomplete_limit: 20,
    autocomplete_prop: 'name',
    autocomplete_value_prop: 'identifier'
};

// base template that is concated with a particular list template (FLOAT or STACK).
honeybee.widgets.AutoComplete.TPL = ''
+ '<input type="text" class="class8 typeahead autocomp-input" '
+ '       data-bind=\'value: current_entry, valueUpdate: "afterkeydown"\' />' 
+ '<input type="hidden" class="class8 typeahead autocomp-input" '
+ '       data-bind="value: current_val, attr: { name: fieldname }" />';