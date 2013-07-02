honeybee.widgets.Reference = honeybee.widgets.Widget.extend({

    log_prefix: "Reference",

    select2_element: null,

    fieldname: null,

    phrase: null,

    is_loading: null,

    realname: null,

    referenced_modules: null,

    iframe: null,

    tags: null,

    enabled_create_targets: null,

    is_visible: null,

    popover_content: null,

    create_reference_click_handle: null,

    // --------------------
    // widget implemenation
    // --------------------

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
        this.is_create_popover_visible = false;
    },

    getTemplate: function()
    {
        return 'static/widgets/Reference.' + this.options.tpl + '.html';
    },

    initGui: function()
    {
        this.parent();

        if (! this.options.readonly)
        {
            if (this.options.autocomplete)
            {
                this.initAutoComplete();
            }

            window.addEventListener('message', this.onDomMessagePostReceived.bind(this), false);
        }

        this.select2_element.popover();
    },

    initKnockoutProperties: function()
    {
        var that = this;

        this.fieldname = ko.observable(this.options.fieldname);
        this.is_loading = ko.observable(false);
        this.tags = ko.observableArray(this.options.tags || []);
        this.referenced_modules = ko.observableArray([]);
        this.reference_exists = ko.observable(true);
        this.enabled_create_targets = ko.observableArray([]);
        this.phrase = ko.observable('');
        
        var that = this;
        this.show_create_button = ko.computed(function()
        {
            return !that.reference_exists() 
                && !that.is_loading() 
                && that.phrase().length > 0;
        });

        var refmodule_name, ref_module;

        var inline_create_targets_list = $('<ul>');
        var inline_create_target_item, inline_create_link;

        for (refmodule_name in this.options.autocomp_mappings)
        {
            ref_module = this.options.autocomp_mappings[refmodule_name];
            ref_module.name = refmodule_name;
            ref_module.active = ko.observable(false);
            this.referenced_modules.push(ref_module);

            inline_create_link = $('<a>');
            inline_create_link.append($('<i class="hb-icon-plus-alt" />'));
            inline_create_link.addClass('create-reference-trigger btn btn-success');
            inline_create_link.attr('href', ref_module.create_url);
            inline_create_link.attr('data-referenced-module', refmodule_name);
            // @todo use ref_module.create_label and replace {phrase}
            inline_create_link.html(
                '<i class="hb-icon-plus-alt" /> ' + ' Kategorie "' + this.phrase() + '" erstellen'
            );

            inline_create_target_item = $('<li>');
            inline_create_target_item.append(inline_create_link);
            inline_create_targets_list.append(inline_create_target_item);
        }

        this.popover_content = $('<div>').append(inline_create_targets_list).html();
    },

    removeTag: function(tag)
    {
        this.tags.remove(tag);
        this.element.find('.tagslist-input').select2('data', this.tags());
    },

    // -------------------------------------------
    // autocomplete handling - select2 integration
    // -------------------------------------------

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
                }, 250);
            },
            formatResult: formatResult,
            formatSelection: formatSelection 
        };

        if (this.options.max && 0 < this.options.max)
        {
            select2_options.maximumSelectionSize = this.options.max;
        }

        this.select2_element.select2(select2_options);
        var input = this.element.find('.select2-input');
        input.on('keyup', function()
        {
            that.phrase($(this).val());
        }).focusout(function()
        {
            that.phrase($(this).val());
            that.hideCreatePopover();
            that.enabled_create_targets([]);
        }).focus(function()
        {
            if (that.enabled_create_targets().length > 0)
            {
                that.showCreatePopover();
            }
        });

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
        var exact_match_found = false;
        var response_count = 0;

        for (module_name in this.options.autocomp_mappings)
        {
            autocomp = this.options.autocomp_mappings[module_name];

            var process_response = function(response, module, options)
            {
                response_count++;

                var label_field = options.display_field;
                var id_field = options.identity_field;

                if (response_count === 1)
                {
                    that.enabled_create_targets([]);
                }

                if (0 < response.data.length)
                {
                    data.results.push({ text: options.module_label });
                }

                var i, entry;
                for (i = 0; i < response.data.length; i++)
                {
                    entry = response.data[i];
                    if (entry[label_field].toLowerCase() == phrase.toLowerCase())
                    {
                        exact_match_found = true;
                    }

                    data.results.push({ 
                        id: entry[id_field],
                        text: entry[label_field],
                        label: options.module_label + ': ' + entry[label_field],
                        module_prefix: module
                    });
                }

                if (!exact_match_found && phrase.length > 0)
                {
                    that.enabled_create_targets.push(module);
                }

                if (that.enabled_create_targets().length === 0)
                {
                    that.hideCreatePopover();
                }
                else
                {
                    that.showCreatePopover();
                }

                callback(data);
            };

            this.is_loading(true);

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
                        setTimeout(function(){ process_response(resp); }, 50);
                        return;
                    }

                    is_processing = true;
                    process_response(resp, module, options);
                    is_processing = false;
                    that.is_loading(false);
                }
            }());
        }
    },

    // --------------------------
    // reference browser handling
    // --------------------------

    initRefbrowser: function()
    {
        var that = this;

        this.iframe = this.element.find('.reference-list-access')[0];
        this.iframe.onload = function()
        {
            that.hideDialog(that.element.find('.modal-reference-loading'));
            that.showDialog(that.element.find('.modal-reference-list'));
        };

        this.element.find('.modal-reference-loading .modal-header .close-dialog').click(function()
        {
            that.hideDialog(that.element.find('.modal-reference-loading'));
        });

        this.element.find('.modal-reference-list .modal-header .close-dialog').click(function()
        {
            that.hideDialog(that.element.find('.modal-reference-list'));
        });
    },

    launchReferenceBrowser: function()
    {
        var loading_modal_el = this.element.find('.modal-reference-loading');
        var refbrowser_modal_el = this.element.find('.modal-reference-list');
        var refmodule = this.referenced_modules()[0];
        var has_active_tab = false;

        if (! this.iframe)
        {
            this.initRefbrowser();
        }
        
        $.each(this.referenced_modules(), function(index, refmodule)
        {
            refmodule.active(false);
        });

        refmodule.active(true);
        this.showDialog(loading_modal_el);
        this.openReferenceListView(refmodule.list_url);

        return false;
    },

    loadReferenceList: function(data, event)
    {
        if (data.active())
        {
            return false;
        }
        
        $.each(this.referenced_modules(), function(index, refmodule)
        {
            refmodule.active(false);
        });

        data.active(true);

        this.openReferenceListView(data.list_url);

        return false;
    },

    openReferenceListView: function(list_url)
    {
        if (! this.iframe)
        {
            this.initRefbrowser();
        }

        this.iframe.src = list_url;
    },

    hideDialog: function(element)
    {
        if (this.options.disable_backdrop)
        {
            element.css('display', 'none');
        }
        else
        {
            element.modal('hide');
        }
    },
    
    showDialog: function(element)
    {
        if (this.options.disable_backdrop)
        {
            element.css('display', 'block');
        }
        else
        {
            element.modal({'show': true, 'backdrop': 'static'});
        }
    },

    // ------------------------------------
    // inline referenced document creation
    // ------------------------------------

    showCreatePopover: function()
    {
        var that = this;
        var popover_links;

        if (! this.is_create_popover_visible)
        {
            this.is_create_popover_visible = true;
            this.select2_element.popover('show');

            popover_links = this.element.find('.popover .create-reference-trigger');
            popover_links.click(function(event)
            {
                that.createReferenceDocument($(event.currentTarget));
                return false;
            });
        }
        // @todo consider multiple target modules e.g.: foreach popover_link as link ...
        popover_links = this.element.find('.popover .create-reference-trigger');
        var link_text = 'Kategorie "' + this.phrase() + '" erstellen';
        var html = '<i class="hb-icon-plus-alt" /><span>' + link_text + '</span>';
        var pos_div = $('<div style="visibility:hidden; float: left; overflow: hidden; height: 0px;" />').append(html);
        $(document.body).append(pos_div);
        var calculated_width = pos_div.width() + 60;

        this.element.find('.popover').css('width', (calculated_width > 250 ? calculated_width : 250) + 'px');
        popover_links.html(html);
        pos_div.remove();
    },

    hideCreatePopover: function()
    {
        this.is_create_popover_visible = false;
        this.select2_element.popover('hide');

        if (this.create_reference_click_handle)
        {
            this.element.find('.popover .create-reference-trigger')
                .off('click', this.create_reference_click_handle);
            this.create_reference_click_handle = null;
        }
    },

    createReferenceDocument: function(create_link_element)
    {   
        var that = this;
        
        var ref_module_name = create_link_element.attr('data-referenced-module');
        var ref_module_settings = this.options.autocomp_mappings[ref_module_name];

        var module_data = {};
        module_data[ref_module_settings.display_field] = this.phrase();

        var post_data = {};
        post_data[ref_module_name] = module_data;

        var create_request = honeybee.core.Request.curry(
            ref_module_settings.create_url, post_data, 'post' 
        );

        var referenced_modules_count = 0;
        $.map(this.options.autocomp_mappings, function(settings, name) {
            referenced_modules_count++;
        });

        create_request(function(response)
        {
            window.postMessage(
                JSON.stringify({
                    'event_type': 'info-message', 'message': ref_module_settings.success_label
                }), 
                that.options.event_origin
            );

            var text = response.data[ref_module_settings.display_field];
            var reference_id = response.data[ref_module_settings.identity_field];
            var displayed_text = text;

            if (referenced_modules_count > 1)
            {
                displayed_text = ref_module_settings.module_label + ': ' + displayed_text;
            }

            that.tags.push({ 
                id: reference_id,
                text: text,
                label: displayed_text,
                module_prefix: ref_module_name
            });

            that.select2_element.val('');
            that.select2_element[0].focus();
        });
    },

    // -------------
    // DOM Messaging
    // -------------

    onDomMessagePostReceived: function(event)
    {
        if(0 !== this.options.event_origin.indexOf(event.origin))
        {
            return;
        }

        var msg_data = JSON.parse(event.data);
        if (msg_data.reference_field !== this.options.realname)
        {
            return;
        }

        if (msg_data.event_type === 'item-removed')
        {
            this.onReferenceItemRemoved(msg_data);
        }
        else if(msg_data.event_type === 'item-added')
        {
            this.onReferenceItemAdded(msg_data);
        }
        else if (msg_data.event_type === 'list-loaded')
        {
            this.onReferenceListLoaded(msg_data);
        }
            
        this.element.find('.tagslist-input').select2('data', this.tags());
    },

    onReferenceListLoaded: function(msg_data)
    {
        var i, doc_ids = [];
        for (i = 0; i < this.tags().length; i++)
        {
            doc_ids.push(this.tags()[i].id);
        }

        if (this.iframe)
        {
            this.iframe.contentWindow.postMessage(
                JSON.stringify({'selected_doc_ids': doc_ids}), 
                this.options.event_origin
            );
        }
    },

    onReferenceItemAdded: function(msg_data)
    {
        var i, allready_added = null;

        var item_data = {
            id: msg_data.item.id,
            text: msg_data.item.text,
            module_prefix: msg_data.item.module,
            label: msg_data.item.text 
        };

        for (i = 0; i < this.tags().length && ! allready_added; i++)
        {
            if (this.tags()[i].id === item_data.id)
            {
                allready_added = this.tags()[i];
            }
        }

        if (! allready_added)
        {
            if (this.tags().length === this.options.max)
            {
                this.tags.pop();
                this.tags.push(item_data);
                this.onReferenceListLoaded();
            }
            else
            {
                this.tags.push(item_data);
            }
        }
    },

    onReferenceItemRemoved: function(msg_data)
    {
        var i, to_remove = null;

        for (i = 0; i < this.tags().length && ! to_remove; i++)
        {
            if (this.tags()[i].id === msg_data.item.id)
            {
                to_remove = this.tags()[i];
            }
        }

        if (to_remove)
        {
            this.tags.remove(to_remove);
        }
    }
});

// ---------
// constants 
// ---------

honeybee.widgets.Reference.DEFAULT_OPTIONS = {
    autobind: true,
    max: 0,
    fieldname: 'tagslist[]',
    autocomplete: false,
    autocomp_mappings: [],
    reference_list_url: "",
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