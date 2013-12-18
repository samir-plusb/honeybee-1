honeybee.widgets.Aggregate = honeybee.widgets.Widget.extend({

    log_prefix: "Aggregate",

    fieldname: null,

    aggregate_list: null,

    templates: null,

    widgets: null,

    init: function(element, options, ready_callback)
    {
        this.templates = {};
        this.widgets = [];
        this.parent(element, options, ready_callback);
    },

    initGui: function()
    {
        var that = this;
        this.aggregate_list = this.element.find('> .aggregates-list');

        this.element.find('.aggregate-module-item').each(function(pos, aggregate_module_item)
        {
            aggregate_module_item = $(aggregate_module_item);
            var tpl_element = aggregate_module_item.find('.aggregate-tpl');
            var doc_type = aggregate_module_item
                .find('.honeybee-js-type')
                .val()
                .split('\\')
                .pop()
                .toLowerCase();

            that.templates[doc_type] = tpl_element.html();
            aggregate_module_item.click(function(add_event)
            {
                add_event.preventDefault();
                that.addAggregate(doc_type);
            });

            tpl_element.remove();
        });

        this.element.find('.aggregates-list .aggregate').each(function(idx, item)
        {
            that.initAggregateListItem($(item));
        });

        this.element.find('.collapse-actions .aggregate-expand-all').click(function()
        {
            that.aggregate_list.find('li').each(function(idx, item)
            {
                $(item).removeClass('collapsed');
            });
        });

        this.element.find('.collapse-actions .aggregate-collapse-all').click(function()
        {
            that.aggregate_list.find('li').each(function(idx, item)
            {
                $(item).addClass('collapsed');
            });
        });
        this.updateAggregateLabels();
    },

    registerDisplayedTextInputs: function(aggregate)
    {
        var first_input = aggregate.find('input[type="text"], textarea').first();
        var that = this;
        first_input.change(function()
        {
            var text = first_input.val();
            if (text.length > 0)
            {
                var short_text = text.substr(0, 40);
                if (short_text.length < text.length)
                {
                    short_text = short_text + ' ...';
                }
                aggregate.find('.input-group-label .displayed_text').text(' ' + short_text);

                that.fire('aggregate-label-changed', [{
                    'field': that.options.fieldname,
                    'element': aggregate
                }]);
            }
        });

        var text = first_input.val();
        if (text && text.length > 0)
        {
            var short_text = text.substr(0, 40);
            if (short_text.length < text.length)
            {
                short_text = short_text + ' ...';
            }
            aggregate.find('.input-group-label .displayed_text').text(' ' + short_text);
        }
    },

    updateAggregateLabels: function()
    {
        this.aggregate_list.find('.aggregate').each(function(idx, aggregate)
        {
            aggregate = $(aggregate);
            var first_input = aggregate.find('input:visible').first();
            var text = first_input.val();
            if (text && text.length > 0)
            {
                var short_text = text.substr(0, 40);
                if (short_text.length < text.length)
                {
                    short_text = short_text + ' ...';
                }
                aggregate.find('.input-group-label .displayed_text').text(' ' + short_text);
            }
        });
    },

    addAggregate: function(doc_type, focus)
    {
        if ('undefined' === $.type(focus))
        {
            focus = true;
        }
        var module_item_markup = this.templates[doc_type];
        var list_item = $('<li class="row-fluid aggregate"></li>');
        list_item.html(module_item_markup);
        this.aggregate_list.append(list_item);

        this.initAggregateListItem(list_item);
        this.renderAggregatePositions();

        list_item.find('textarea.ckeditor').each(function(idx, textarea)
        {
            $(textarea).css({'margin-left': '180px', 'margin-right': '50px'});
            CKEDITOR.replace(textarea);
        });

        if (focus)
        {
            var first_input = list_item.find('input:visible, textarea:visible, select:visible').first();
            if (first_input.length > 0) {
                $('html, body').animate({scrollTop: first_input.offset().top - 200}, 350, function()
                {
                    first_input.focus();
                });
            } else {
                $('html, body').animate({scrollTop: list_item.offset().top - 200}, 350);
            }
        }
        this.fire('aggregate-added', [{
            'field': this.options.fieldname,
            'element': list_item
        }]);
        if (this.aggregate_list.find('.aggregate').length > 1) {
            this.element.find('.aggregate-selector-bottom').show();
        } else {
            this.element.find('.aggregate-selector-bottom').hide();
        }
        return list_item;
    },

    initAggregateListItem: function(aggregate_element)
    {
        var loading_widgets_cnt = 0;
        var that = this;

        this.registerAggregateEvents(aggregate_element);
        this.registerDisplayedTextInputs(aggregate_element);
        aggregate_element[0].widgets = [];
        aggregate_element.find('.honeybee-widget').each(function(idx, element)
        {
            var widget = that.initWidget(element, function()
            {
                loading_widgets_cnt--;
                if (loading_widgets_cnt === 0)
                {
                    that.renderAggregatePositions();
                }
            });
            if (widget) {
                loading_widgets_cnt++;
                aggregate_element[0].widgets.push(widget);
            }
        });
    },

    initWidget: function(element, ready_callback)
    {
        var type_key;

        $.each($(element).attr('class').split(' '), function(index, css_class)
        {
            css_class = css_class.trim();
            if (css_class.match(/^widget-/))
            {
                type_key = css_class.replace('widget-', '');
            }
        });

        if (type_key)
        {
            return honeybee.widgets.Widget.factory(element, type_key, honeybee.widgets, ready_callback)
        }
        return false;
    },

    registerAggregateEvents: function(item)
    {
        var that = this;
        item.find('.actions .aggregate-remove').click(function(remove_event)
        {
            that.removeItem(item);
            return false;
        });

        item.find('.actions .aggregate-up').click(function(move_up_event)
        {
            that.moveItemUp(item);
            return false;
        });

        item.find('.actions .aggregate-down').click(function(move_down_event)
        {
            that.moveItemDown(item);
            return false;
        });
        var timer = null;
        item.find('.input-group-label').click(function(expand_event)
        {
            if (item.hasClass('collapsed'))
            {
                item.removeClass('collapsed');
                var first_input = item.find('input:visible, textarea:visible, select:visible').first();
                if (first_input.length > 0) {
                    first_input.focus();
                    $('html, body').animate({scrollTop: first_input.offset().top - 200}, 350, function()
                    {
                        if (timer) {
                            clearTimeout(timer);
                            timer = null;
                        }
                        timer = setTimeout(function() { first_input.focus(); }, 500);
                    });
                } else {
                    $('html, body').animate({scrollTop: item.offset().top - 200}, 350);
                }
            }
            else
            {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                item.addClass('collapsed');
            }
        });
    },

    moveItemUp: function(item)
    {
        var restore_richtext = this.createTextareaBackups(item);

        item.insertBefore(item.prev());
        this.renderAggregatePositions();
        this.fire('aggregate-moved-up', [{
            'field': this.options.fieldname,
            'element': item
        }]);

        restore_richtext();
    },

    moveItemDown: function(item)
    {
        var restore_richtext = this.createTextareaBackups(item);

        item.insertAfter(item.next());
        this.renderAggregatePositions();
        this.fire('aggregate-moved-down', [{
            'field': this.options.fieldname,
            'element': item
        }]);

        restore_richtext();
    },

    removeItem: function(item)
    {
        item.remove();
        this.renderAggregatePositions();

        this.fire('aggregate-removed', [{
            'field': this.options.fieldname,
            'element': item[0]
        }]);
        if (this.aggregate_list.find('.aggregate').length > 1) {
            this.element.find('.aggregate-selector-bottom').show();
        } else {
            this.element.find('.aggregate-selector-bottom').hide();
        }
    },

    // workaround for ckeditor and epic-editor breaking when a surrounding dom-container is moved.
    // sad but true ...
    // @see http://ckeditor.com/forums/CKEditor-3.x/Moving-CKEditor-instances-around-DOM
    createTextareaBackups: function(item)
    {
        // collect all markdown-widget instances to reset after moving.
        var i = 0;
        var markdown_widgets_to_reload = [];
        var aggregate_widgets = item[0].widgets || [];
        for (; i < aggregate_widgets.length; i++) {
            if (aggregate_widgets[i] instanceof honeybee.widgets.MarkdownWidget) {
                markdown_widgets_to_reload.push(aggregate_widgets[i]);
            }
        }
        // collect all ckeditor instances to reset after moving.
        var textareas_to_recreate = [];
        item.find('.cke').each(function(idx, ck_editor_el){
            var cke_id = $(ck_editor_el).attr('id');
            var input_id = $(ck_editor_el).attr('id').replace('cke_','');
            CKEDITOR.instances[input_id].destroy();
            textareas_to_recreate.push(input_id);
        });
        // return a closure that can be called to restore the textarea-editor states prior to movement.
        return function() {
            var n;
            // reload ckeditor instances
            for (n = 0; n < textareas_to_recreate.length; n++) {
                CKEDITOR.replace(textareas_to_recreate[n]);
            }
            // reload epic-editor instances inside markdown-widgets
            for (n = 0; n < markdown_widgets_to_reload.length; n++) {
                markdown_widgets_to_reload[n].loadEpicEditor();
            }
        };
    },

    renderAggregatePositions: function()
    {
        this.element.find('> .aggregates-list .aggregate').each(function(idx, element)
        {
            $(element).find('.input-group-label .position').text('#' + (idx + 1));
            // @todo include select, radio etc.
            $(element).find('input, textarea').each(function(pos, input)
            {
                var input = $(input);
                var name = input.attr('name');
                if (name)
                {
                    input.attr(
                        'name',
                        name.replace(/\[\d+\]/, '[' + idx + ']')
                    );
                }
            });

            var i = 0;
            var cur_widget, fieldname;
            for (; element.widgets && i < element.widgets.length; i++)
            {
                cur_widget = element.widgets[i];
                if (!cur_widget.fieldname) {
                    // @todo shouldn't happen, but it does :S
                    // find out why ...
                    continue;
                }
                fieldname = cur_widget.fieldname();
                fieldname = fieldname.replace(/\[\d+\]/, '[' + idx + ']');
                cur_widget.fieldname(fieldname);
            }
        });
    },

    getTemplate: function()
    {
        return false;
    }
});
