honeybee.widgets.Reference = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "Reference",

    select2_element: null,

    reference_list_modal: null,

    // <knockout_props>
    fieldname: null,

    realname: null,

    tags: null,
    // </knockout_props>

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/Reference.' + this.options.tpl + '.html';
    },

    initGui: function()
    {
        this.parent();
        var that = this;

        if (this.options.autocomplete)
        {
            this.initAutoComplete();
        }

        var messageEventHandler = function(event)
        {
            if(event.origin == 'http://cms.familienportal.dev')
            {
                var msg_data = JSON.parse(event.data);
                if (msg_data.reference_field == that.options.realname)
                {
                    if (msg_data.event_type === 'item-removed')
                    {
                        var to_remove = null;
                        for (var i = 0; i < that.tags().length && ! to_remove; i++)
                        {
                            if (that.tags()[i].id === msg_data.item.id)
                            {
                                to_remove = that.tags()[i];
                            }
                        }
                        if (to_remove)
                        {
                            that.tags.remove(to_remove);
                        }
                    }
                    else if(msg_data.event_type === 'item-added')
                    {
                        var allready_added = null;
                        var item_data = {
                            id: msg_data.item.id,
                            text: msg_data.item.text,
                            module_prefix: msg_data.item.module,
                            label: msg_data.item.text 
                        };
                        for (var i = 0; i < that.tags().length && ! to_remove; i++)
                        {
                            if (that.tags()[i].id === item_data.id)
                            {
                                allready_added = that.tags()[i];
                            }
                        }
                        if (! allready_added)
                        {
                            that.tags.push(item_data);
                        }
                    }
                    
                    that.element.find('.tagslist-input').select2('data', that.tags());
                }
            }
        }
        window.addEventListener('message', messageEventHandler,false);
    },

    initKnockoutProperties: function()
    {
        var that = this;
        this.fieldname = ko.observable(this.options.fieldname);
        this.tags = ko.observableArray(this.options.tags || [ ]);
    },

    removeTag: function(tag)
    {
        this.tags.remove(tag);
        this.element.find('.tagslist-input').select2('data', this.tags());
    },

    openReferenceListView: function()
    {
        var that = this;
        this.element.find('.reference-list-access')[0].onload = function()
        {
            that.reference_list_modal = that.element.find('.modal-reference-list').first().modal({'show': true});
        };

        this.element.find('.reference-list-access')[0].contentDocument.location.reload(true);
    },

    // #########################
    // #     working funcs     #
    // #########################
    initAutoComplete: function()
    {
        this.select2_element = this.element.find('.tagslist-input');

        var formatResult = function(result, element, query) 
        {
            if (! result.id)
            {
                return '<strong style="color: rgb(0, 136, 204);">' + result.text + '</strong>';
            }

            var match = query ? result.text.toUpperCase().indexOf(
                query.term.toUpperCase()
            ) : -1;

            if (match < 0) 
            {
                return result.text;
            }

            
            var parts = result.text.split(' ');
            var markup = [], i;

            for (i = 0; i < parts.length; i++)
            {
                if (0 === parts[i].toUpperCase().indexOf(query.term.toUpperCase()))
                {
                    parts[i] = "<b>" + parts[i].substring(0, query.term.length) + "</b>" +
                        parts[i].substring(query.term.length, parts[i].length);
                }
                markup.push(parts[i]);
            }

            return markup.join(' ');
        };

        var formatSelection = function(result, element, query)
        {
            if (! result.id)
            {
                return '<strong style="color: rgb(0, 136, 204);">' + result.text + '</strong>';
            }

            return result.text;
        };

        var that = this, autocomplete_timer;

        var select2_options = {
            placeholder: this.options.texts.placeholder,
            minimumInputLength: 0,
            multiple: true,
            formatSelectionTooBig: function() { return that.options.texts.too_long; },
            formatInputTooShort: function() { return that.options.texts.too_short; },
            formatSearching: function() { return that.options.texts.searching; },
            formatNoMatches: function() { return that.options.texts.no_results; },
            containerCssClass: "refwidget-container",
            query: function (query) 
            {
                window.clearTimeout(autocomplete_timer);
                autocomplete_timer = window.setTimeout(function()
                {
                    that.fetchData(query.term, query.callback);
                }, 300);
            },
            formatResult: formatResult,
            formatSelection: formatSelection 
        };

        if (this.options.max && 0 < this.options.max)
        {
            select2_options.maximumSelectionSize = this.options.max;
        }

        this.select2_element.select2(select2_options);

        this.select2_element.on('change', function(event)
        {
            if (event.removed)
            {
                that.removeTag(event.removed);
            }
            
            if (event.added)
            {
                that.tags.push(event.added);
            }
        });

        this.element.find('.tagslist-input').select2('data', this.tags());
    },

    fetchData: function(phrase, callback)
    {
        var module_name, autocomp, that = this;
        var data = { results: [] };
        var is_processing = false;

        for (module_name in this.options.autocomp_mappings)
        {
            autocomp = this.options.autocomp_mappings[module_name];

            var process_response = function(response, module, options)
            {
                var label_field = options.display_field;
                var id_field = options.identity_field;

                if (0 < response.data.length)
                {
                    data.results.push({ text: options.module_label });
                }

                var i, entry;
                for (i = 0; i < response.data.length; i++)
                {
                    entry = response.data[i];
                    data.results.push({ 
                        id: entry[id_field],
                        text: entry[label_field],
                        label: options.module_label + ': ' + entry[label_field],
                        module_prefix: module
                    });
                }

                callback(data);
            };

            honeybee.core.Request.curry(
                autocomp.uri.replace('{PHRASE}', encodeURIComponent(phrase))
            )(function()
            {
                var module = module_name;
                var options = autocomp;

                return function(resp)
                {
                    if (is_processing)
                    {
                        setTimeout(function(){ process_response(resp); }, 200);
                        return;
                    }

                    is_processing = true;
                    process_response(resp, module, options);
                    is_processing = false;
                }
            }());
        }
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.Reference.DEFAULT_OPTIONS = {
    autobind: true,
    max: 0,
    fieldname: 'tagslist[]',
    autocomplete: false,
    autocomp_mappings: [],
    texts: {
        placeholder: '',
        too_short: '',
        too_long: '',
        searching: '',
        no_results: ''
    },
    restrict_values: true,
    allow_duplicates: false,
    tpl: "Float"
};

honeybee.widgets.Reference.TPL = {
    FLOAT: "Float",
    STACK: "Stack",
    INLINE: "Inline"
}