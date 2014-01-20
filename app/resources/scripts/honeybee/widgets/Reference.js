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

    create_reference_click_handle: null,

    pending_request: null,

    queued_requests: null,

    // --------------------
    // widget implemenation
    // --------------------

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
        this.is_create_popover_visible = false;
        this.pending_request = null;
        this.queued_requests = null;
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

        if (this.options.enable_inline_create)
        {
            var input = this.element.find('.refwidget-container');
            var edge_distance_right = $(window).width() - (input.width() + input.offset().left);
            var edge_distance_left = input.offset().left;
            var placement;
            var avail_popup_space = Math.max(edge_distance_left, edge_distance_right);
            if (avail_popup_space > 400) {
                placement = (edge_distance_left > edge_distance_right) ? 'left' : 'right';
            } else {
                placement = 'top';
            }
            input.popover({
                trigger: 'manual',
                animation: true,
                html: true,
                title: this.options.texts.inline_create_label,
                placement: placement,
                content: this.buildPopover()
            });
        }

        window.postMessage(
            JSON.stringify({
                'type': 'references-rendered',
                'field': this.options.realname,
                'references': this.tags(),
                'field_id': this.options.field_id
            }),
            this.options.event_origin
        );
    },

    buildPopover: function()
    {
        var refmodule_name, ref_module;

        var inline_create_targets_list = $('<ul>');
        var inline_create_target_item, inline_create_link;

        for (refmodule_name in this.options.autocomp_mappings)
        {
            ref_module = this.options.autocomp_mappings[refmodule_name];

            inline_create_link = $('<a>');
            inline_create_link.append($('<i class="hb-icon-plus-alt" />'));
            inline_create_link.addClass('create-reference-trigger btn btn-success');
            inline_create_link.attr('href', ref_module.create_url);
            inline_create_link.attr('data-referenced-module', refmodule_name);
            inline_create_link.html(
                '<i class="hb-icon-plus-alt" /><span>' + ref_module.create_label + '</span>'
            );

            inline_create_target_item = $('<li>');
            inline_create_target_item.append(inline_create_link);
            inline_create_targets_list.append(inline_create_target_item);
        }

        return $('<div>').append(inline_create_targets_list).html();
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
        for (refmodule_name in this.options.autocomp_mappings)
        {
            ref_module = this.options.autocomp_mappings[refmodule_name];
            ref_module.name = refmodule_name;
            ref_module.active = ko.observable(false);
            this.referenced_modules.push(ref_module);
        }
    },

    removeTag: function(tag)
    {
        this.tags.remove(tag);
        this.select2_element.select2('data', this.tags());
        window.postMessage(
            JSON.stringify({
                'type': 'reference-removed',
                'reference': tag,
                'field': this.options.realname,
                'field_id': this.options.field_id
            }),
            this.options.event_origin
        );
    },

    // -------------------------------------------
    // autocomplete handling - select2 integration
    // -------------------------------------------

    initAutoComplete: function()
    {
        this.select2_element = this.element.find('.tagslist-input');

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
            formatResult: this.formatSelect2Result,
            formatSelection: this.formatSelect2Selection
        };

        if (this.options.max && 0 < this.options.max)
        {
            select2_options.maximumSelectionSize = this.options.max;
        }

        this.select2_element.select2(select2_options);
        var input = this.element.find('.select2-input');
        input.on('keyup', function(event)
        {
            that.phrase($(this).val());
            if (! event.shiftKey && event.keyCode == 9 && that.is_create_popover_visible)
            {
                var create_btns = that.element.find('.popover .create-reference-trigger:not(.disabled)');
                if (create_btns.length > 0)
                {
                    create_btns[0].focus();
                }
            }
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
                window.postMessage(
                    JSON.stringify({
                        'type': 'reference-added',
                        'reference': event.added,
                        'field': that.options.realname,
                        'field_id': that.options.field_id
                    }),
                    that.options.event_origin
                );
            }
        }).on('close', function()
        {
            that.phrase($(this).val());
            that.hideCreatePopover();
            that.enabled_create_targets([]);
        });

        this.select2_element.select2('data', this.tags());
    },

    formatSelect2Result: function(result, element, query)
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
    },

    formatSelect2Selection: function(result, element, query)
    {
        if (! result.id)
        {
            return '<strong style="color: rgb(0, 136, 204);">' + result.text + '</strong>';
        }

        return result.text;
    },

    fetchData: function(phrase, callback)
    {
        if (this.pending_request)
        {
            this.pending_request.abort();
            this.pending_request = null;
        }
        this.queued_requests = [];
        this.enabled_create_targets([]);

        var module_name, autocomp, that = this;
        var data = { results: [] };

        for (module_name in this.options.autocomp_mappings)
        {
            autocomp = this.options.autocomp_mappings[module_name];

            // need to conserve the current values for module_name
            // inside a closure as it changes for every loop.
            var response_handle = (function()
            {
                var module = module_name;
                return function(resp)
                {
                    var select2_state = { data: data, callback: callback };
                    that.proccessSuggestResponse(resp, phrase, module, select2_state);

                    if (that.queued_requests.length > 0)
                    {
                        var request = that.queued_requests.shift();
                        that.pending_request = request.send(request.handle);
                    }
                    else
                    {
                        that.is_loading(false);
                    }
                }
            })();
            // queue up groups of each a request and it's related repsonse handle.
            this.queued_requests.push({
                'send': honeybee.core.Request.curry(
                    autocomp.uri.replace('{PHRASE}', encodeURIComponent(phrase))
                ),
                'handle': response_handle
            });
        }
        // then start processing the request queue
        this.is_loading(true);
        var request = this.queued_requests.shift();
        this.pending_request = request.send(request.handle);
    },

    proccessSuggestResponse: function(response, phrase, module_name, select2_state)
    {
        var exact_match_found = false;
        var options = this.options.autocomp_mappings[module_name];
        var label_field = options.display_field;
        var id_field = options.identity_field;

        if (0 < response.data.length)
        {
            select2_state.data.results.push({ text: options.module_label });
        }

        var i, entry;
        for (i = 0; i < response.data.length; i++)
        {
            entry = response.data[i];
            if (entry[label_field].toLowerCase() == phrase.toLowerCase())
            {
                exact_match_found = true;
            }
            // var item_label = options.module_label + ': ' + entry[label_field];
            var item_label = entry[label_field];
            select2_state.data.results.push({
                id: entry[id_field],
                text: entry[label_field],
                label: item_label,
                module_prefix: module_name
            });
        }

        if (!exact_match_found && phrase.length > 0)
        {
            this.enabled_create_targets.push(module_name);
        }

        if (this.queued_requests.length === 0)
        {
            if (this.enabled_create_targets().length === 0)
            {
                this.hideCreatePopover();
            }
            else
            {
                this.showCreatePopover();
            }

            select2_state.callback(select2_state.data);
        }
    },

    // --------------------------
    // reference browser handling
    // --------------------------

    initRefbrowser: function()
    {
        var that = this;
        var $reference_dialog = that.element.find('.modal-reference-list');
        var resizeDialog = this.buildResizeFunction($reference_dialog);

        this.iframe = this.element.find('.reference-list-access')[0];
        this.iframe.onload = function()
        {
            that.hideDialog(that.element.find('.modal-reference-loading'));
            resizeDialog();
            that.showDialog($reference_dialog);
            window.onresize = resizeDialog;
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

    buildResizeFunction: function($reference_dialog)
    {
        var that = this;
        var $dialog_body = $reference_dialog.find('.modal-body');

        return function()
        {
            var refbrowser_width = $(window).width() - 100;
            var refbrowser_height = $(window).height() - 100;
            var center_margin = (refbrowser_width / 2);
            var middle_margin = (refbrowser_height / 2);
            var body_height = refbrowser_height - 82;

            var resize_options;
            if (that.iframe.contentWindow.parent === window.top) {
                resize_options = {
                    'width': refbrowser_width,
                    'height': refbrowser_height,
                    'margin-top': -middle_margin,
                    'margin-left': -center_margin
                };
            } else {
                resize_options = {
                    'width': refbrowser_width + 100,
                    'height': refbrowser_height + 100,
                    'margin-top': -(middle_margin + 50),
                    'margin-left': -(center_margin + 50)
                };
            }

            $reference_dialog.css(resize_options);
            $dialog_body.css({
                'height': body_height,
                'padding': 0,
                'max-height': body_height
            });
        };
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
        var query_string = $.url(refmodule.list_url).attr('query');
        var ref_browser_url = refmodule.list_url + query_string;
        ref_browser_url += query_string.length > 0 ? '&' : '?';
        ref_browser_url += 'referenceFieldId=' + this.options.field_id;
        this.openReferenceListView(ref_browser_url);

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

        var ref_browser_url = data.list_url + '&referenceFieldId=' + this.options.field_id;
        this.openReferenceListView(ref_browser_url);

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

        window.top.postMessage(
            JSON.stringify({
                'event_type': 'reference-browser-hidden',
                'fieldname': this.options.fieldname,
                'field_id': this.options.field_id,
                'element_classes': element.attr('class')
            }),
            this.options.event_origin
        );
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

        window.top.postMessage(
            JSON.stringify({
                'event_type': 'reference-browser-shown',
                'fieldname': this.options.fieldname,
                'field_id': this.options.field_id,
                'element_classes': element.attr('class')
            }),
            this.options.event_origin
        );
    },

    // ------------------------------------
    // inline referenced document creation
    // ------------------------------------

    showCreatePopover: function()
    {
        var that = this;
        var popover_links;

        if (! this.options.enable_inline_create)
        {
            return;
        }

        if (! this.is_create_popover_visible)
        {
            this.is_create_popover_visible = true;
            this.element.find('.refwidget-container').popover('show');

            popover_links = this.element.find('.popover .create-reference-trigger');
            popover_links.mousedown(function(event)
            {
                return false;
            }).click(function(event)
            {
                that.createReferenceDocument($(event.currentTarget));
                that.select2_element.select2('close');
                event.preventDefault();
            });
        }

        var popover_max_width = 250;
        this.element.find('.popover .create-reference-trigger').each(function(index, link_element)
        {
            link_element = $(link_element);
            var module_name = link_element.attr('data-referenced-module');

            if (that.enabled_create_targets().indexOf(module_name) > -1)
            {
                link_element.removeClass('disabled');
            }
            else
            {
                link_element.addClass('disabled');
            }

            var settings = that.options.autocomp_mappings[module_name];
            var link_text = settings.create_label.replace('{phrase}', that.phrase());
            var html = '<i class="hb-icon-plus-alt" /><span>' + link_text + '</span>';
            // render the create button offscreen to find out how wide it will be.
            var pos_div = $(
                '<div style="visibility:hidden; float: left; overflow: hidden; height: 0px;" />'
            ).append(html);

            $(document.body).append(pos_div);

            // then calculate the resulting popover width and inject the button.
            var calculated_width = pos_div.width() + 60;
            popover_max_width = (popover_max_width > calculated_width) ? popover_max_width : calculated_width;
            link_element.html(html);
            pos_div.remove();
        });

        var popover_el = this.element.find('.popover');
        var prev_width = popover_el.width();
        popover_el.css('width', popover_max_width + 'px');

        var input = this.element.find('.refwidget-container');
        var edge_distance_right = $(window).width() - (input.width() + input.offset().left);
        var edge_distance_left = input.offset().left;
        var css_adjsutments = {};

        if (edge_distance_left > edge_distance_right)
        {
            var delta_width = popover_max_width - prev_width;
            var new_left = popover_el.position().left - delta_width;
            css_adjsutments.left = new_left + 'px';
        }

        popover_el.css(css_adjsutments);
    },

    hideCreatePopover: function()
    {
        if (! this.options.enable_inline_create)
        {
            return;
        }

        this.is_create_popover_visible = false;
        this.element.find('.refwidget-container').popover('hide');

        if (this.create_reference_click_handle)
        {
            this.element.find('.popover .create-reference-trigger')
                .off('click', this.create_reference_click_handle);
            this.create_reference_click_handle = null;
        }
    },

    createReferenceDocument: function(create_link_element)
    {
        if (create_link_element.hasClass('disabled'))
        {
            this.element.find('.select2-input')[0].focus();
            return;
        }
        create_link_element.addClass('disabled');

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

        this.is_loading(true);
        create_request(function(response)
        {
            that.is_loading(false);

            if (response.state === 'error')
            {
                window.postMessage(
                    JSON.stringify({
                        'event_type': 'error-message',
                        'message': response.errors[0]
                    }),
                    that.options.event_origin
                );
                return;
            }

            window.postMessage(
                JSON.stringify({
                    'event_type': 'info-message',
                    'message': ref_module_settings.success_label
                }),
                that.options.event_origin
            );

            var text = response.data[ref_module_settings.display_field];
            var reference_id = response.data[ref_module_settings.identity_field];
            // var displayed_text = ref_module_settings.module_label + ': ' + text;
            var displayed_text = text;

            that.hideCreatePopover();

            that.tags.push({
                id: reference_id,
                text: text,
                label: displayed_text,
                module_prefix: ref_module_name
            });

            window.postMessage(
                JSON.stringify({
                    'type': 'reference-added',
                    'reference': event.added,
                    'field': that.options.realname,
                    'field_id': that.options.field_id
                }),
                that.options.event_origin
            );

            that.select2_element.select2('data', that.tags());
            that.highlightTag(that.tags().length - 1, function()
            {
                that.select2_element.select2('open');
            });
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
        if (msg_data.reference_field_id !== this.options.field_id)
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
            label: msg_data.item.text,
            field_id: this.options.field_id
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
            if (this.options.max > 0 && this.tags().length === this.options.max)
            {
                var removed = this.tags.pop();
                window.postMessage(
                    JSON.stringify({
                        'type': 'reference-removed',
                        'reference': removed,
                        'field': this.options.realname,
                        'field_id': this.options.field_id
                    }),
                    this.options.event_origin
                );

                this.tags.push(item_data);
                this.onReferenceListLoaded();
            }
            else
            {
                this.tags.push(item_data);
            }

            window.postMessage(
                JSON.stringify({
                    'type': 'reference-added',
                    'reference': item_data,
                    'field': this.options.realname,
                    'field_id': this.options.field_id
                }),
                this.options.event_origin
            );
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
            window.postMessage(
                JSON.stringify({
                    'type': 'reference-removed',
                    'reference': to_remove,
                    'field': this.options.realname,
                    'field_id': this.options.field_id
                }),
                this.options.event_origin
            );
        }
    },

    highlightTag: function(reference_position, finished_callback)
    {
        var list = this.element.find('.tagslist-list').first();
        finished_callback = finished_callback || function() {};
        var fadein = true;
        var el = $(list.find('.tagslist-tag')[reference_position]);
        list.scrollTop(
            el.offset().top - list.offset().top + list.scrollTop()
        );

        var reset_state = function()
        {
            el.removeClass('highlight');
            el.unbind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
            finished_callback();
            return;
        };
        el.bind('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', reset_state);
        el.addClass('highlight');
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