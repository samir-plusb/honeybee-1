honeybee.widgets.TagsList = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "TagsList",

    currently_valid: null,

    placeholder: null,

    // <knockout_props>
    fieldname: null,

    tags: null,

    current_tag: null,

    has_focus: null,

    is_valid: null,

    max_reached: null,

    enabled: null,

    disabled: null,

    disabled_text: null,
    // </knockout_props>

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
        this.currently_valid = {};
        if (this.options.autocomplete === true) {
            this.placeholder = 'Auswählen';
        } else {
            this.placeholder = 'Hinzufügen (Freitext)';
        }
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/TagsList.' + this.options.tpl + '.html';
    },

    initGui: function()
    {
        this.parent();
        // setup autocomplete when activated
        if (this.options.autocomplete)
        {
            this.initTypeahead();
        }
    },

    initKnockoutProperties: function()
    {
        var that = this;
        var fname = this.options.fieldname;
        this.fieldname = ko.observable(fname + (1 === this.options.max ? '' : '[]'));
        this.tags = ko.observableArray(this.options.tags || [ ]);
        this.current_tag = ko.observable('');
        this.has_focus = ko.observable(false);
        this.max_reached = ko.observable(false);
        this.is_valid = ko.observable(false);
        this.disabled = ko.observable(false);
        this.disabled_text = ko.observable('');
        this.enabled = ko.computed(function()
        {
            return (that.is_valid() && ! that.max_reached() && ! that.disabled());
        });
        this.current_tag.subscribe(function(new_value)
        {
            that.validate();
        });
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    onInput: function(data, event)
    {
        if (this.options.readonly)
        {
            return;
        }

        if (13 === event.which)
        {
            if (this.is_valid())
            {
                this.addCurrentTag(); // 'enter' was pressed and tag is valid, so add it
            }
            return false;
        }
        return true;
    },

    busyStart: function(text)
    {
        this.disabled(true);
        this.disabled_text(text);
    },

    busyEnd: function()
    {
        this.disabled(false);
        this.disabled_text('');
    },

    validate: function()
    {
        var tag = this.current_tag();
        var valid = true;
        if (this.options.autocomplete && this.options.restrict_values)
        {
            valid = ('undefined' !== typeof this.currently_valid[tag]);
        }
        this.is_valid(
            valid && 0 < tag.length && (0 === this.options.max || this.options.max > this.tags().length)
        );
        return this.is_valid();
    },

    addCurrentTag: function()
    {
        if (this.options.readonly)
        {
            return;
        }

        var tag = this.current_tag();
        var idx = -1;
        for (var i = 0; i < this.tags().length; i++) // search for tag inside allready added tags
        {
            if (tag === this.tags()[i].label)
            {
                idx = i; // tag found
                break;
            }
        }
        if (-1 === idx || this.options.allow_duplicates)
        {
            this.tags.push({ // tag not found or duplicates allowed, so add it
                label: tag,
                value: this.options.autocomplete ? this.currently_valid[tag] : tag
            });
            this.current_tag('');
            this.fire('tagschanged', [ this.fieldname(), this.tags()]);
        }
        else
        {
            this.highlightTag(idx); // tag allready exists, highlight to point this out ^^
        }
        this.has_focus(true); // auto (re)gain focus for better usability
    },

    removeTag: function(tag)
    {
        if (this.options.readonly)
        {
            return;
        }

        this.tags.remove(tag);
        this.fire('tagschanged', [ this.fieldname(), this.tags()]);
    },

    // ##############################
    // #     autocomplete funcs     #
    // ##############################
    initTypeahead: function()
    {
        if (this.options.readonly)
        {
            return;
        }

        var that = this;
        var input = this.element.find('.tagslist-input');
        input.typeahead({
            property: this.options.autocomplete_display_prop,
            source: this.fetchAutoCompleteData.bind(this),
            items: this.options.autocomplete_limit,
            matcher: function () { return true; },
            highlighter: function (item) {
              return item.replace(new RegExp('^(' + this.query + ')', 'i'), function ($1, match) {
                return '<u>' + match + '</u>'
              })
            },
            minLength: 0,
            onselect: function(val)
            {
                that.current_tag( // apply the selected autocomplete value
                    val[that.options.autocomplete_display_prop]
                );
                if (that.is_valid())
                {
                    that.addCurrentTag(); // directly add tag when selected from autocomplete list and it is valid
                }
            }
        });
        input.focus(function(){ that.fetchAutoCompleteData(null, input.val()); });
    },

    fetchAutoCompleteData: function(typeahead, phrase)
    {
        // synchron autocomplete based on local values from our options
        if (this.options.autocomplete_values) {
            if (!phrase || 0 === phrase.length) {
                this.setAutoCompleteData(this.options.autocomplete_values);
            } else {
                // filter our local values based on the given phrase.
                var autocomp_values = [];
                var n, label, value, regexp;
                for (n = 0; n < this.options.autocomplete_values.length; n++) {
                    label = this.options.autocomplete_values[n].label;
                    value = this.options.autocomplete_values[n].value;
                    regexp = new RegExp('^'+phrase, 'i');
                    if (label.match(regexp)) {
                        autocomp_values.push({'value': value, 'label': label});
                    }
                }
                this.setAutoCompleteData(autocomp_values);
            }
            return;
        }
        // asynchron autocomplete based on values returned from querying
        // a server at the configured autocomplete url.
        var that = this;
        if (1 >= phrase.length)
        {
            that.setAutoCompleteData([]);
            return;
        }
        honeybee.core.Request.curry(
            that.options.autocomplete_uri.replace('{PHRASE}', phrase)
        )(function(resp) { that.setAutoCompleteData(resp.data); });
    },

    setAutoCompleteData: function(data)
    {
        var i;
        var selected_values = {};
        var autocomplete_values = [];
        for (i = 0; i < this.tags().length; i++) {
            selected_values[this.tags()[i].value] = true;
        }
        for (i = 0; i < data.length; i++) {
            var label = data[i].label;
            var value = data[i].value;
            if (selected_values[value] !== true) {
                this.currently_valid[label] = value;
                autocomplete_values.push(data[i]);
            }
        }
        this.element.find('.tagslist-input').typeahead('process', [autocomplete_values]);
    },

    highlightTag: function(tag_idx)
    {
        var fadein = true;
        var el = $(this.element.find('.tagslist-list .tagslist-tag')[tag_idx]);
        var reset_state = function()
        {
            el.removeClass('highlight');
            el.unbind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
            return;
        };
        el.bind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
        el.addClass('highlight');
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.TagsList.DEFAULT_OPTIONS = {
    autobind: true,
    max: 0,
    fieldname: 'tagslist[]',
    autocomplete: false,
    autocomplete_uri: null,
    autocomplete_limit: 20,
    autocomplete_display_prop: 'name',
    autocomplete_value_prop: null,
    restrict_values: true,
    allow_duplicates: false,
    tpl: "Float"
};

honeybee.widgets.TagsList.TPL = {
    FLOAT: "Float",
    STACK: "Stack",
    INLINE: "Inline"
}