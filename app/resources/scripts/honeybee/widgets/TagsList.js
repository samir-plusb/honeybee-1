honeybee.widgets.TagsList = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "TagsList",

    currently_valid: null,

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
        this.tags.remove(tag);
        this.has_focus(true);
        this.fire('tagschanged', [ this.fieldname(), this.tags()]);
    },

    // #########################
    // #     working funcs     #
    // #########################
    initTypeahead: function()
    {
        var that = this;
        this.element.find('.tagslist-input').typeahead({
            property: this.options.autocomplete_display_prop,
            source: this.fetchTypeAheadData.bind(this),
            items: this.options.autocomplete_limit,
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
            var data = resp.data;
            that.currently_valid = {};
            for (var i = 0; i < data.length; i++)
            {
                that.currently_valid[data[i][that.options.autocomplete_display_prop]] = data[i][that.options.autocomplete_value_prop];
            }

            typeahead.process(data);
        });
    },

    highlightTag: function(tag_idx)
    {
        var fadein = true;
        var el = $(this.element.find('.tagslist-list li')[tag_idx]);
        var reset_state = function()
        {
            if (! fadein)
            {
                el.removeClass('highlight-transition');
                el.unbind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
                return;
            }
            fadein = false;
            el.removeClass('highlight');
        };
        el.bind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
        el.addClass('highlight-transition').addClass('highlight');
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