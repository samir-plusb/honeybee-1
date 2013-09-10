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
    },

    registerDisplayedTextInputs: function(aggregate)
    {
        var first_input = aggregate.find('input').first();
        var that = this;
        first_input.change(function()
        {
            var text = first_input.val();
            if (text.length > 0)
            {
                var short_text = text.substr(0, 25);
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
        if (text.length > 0)
        {
            var short_text = text.substr(0, 25);
            if (short_text.length < text.length)
            {
                short_text = short_text + ' ...';
            }
            aggregate.find('.input-group-label .displayed_text').text(' ' + short_text);
        }
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
            var first_input = list_item.find('input').first();
            $('html, body').animate({scrollTop: first_input.offset().top - 200}, 350, function()
            {
                first_input.focus();
            });
        }
        this.fire('aggregate-added', [{
            'field': this.options.fieldname,
            'element': list_item
        }]);

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
                loading_widgets_cnt++;
                aggregate_element[0].widgets.push(
                    honeybee.widgets.Widget.factory(element, type_key, honeybee.widgets, function()
                    {
                        loading_widgets_cnt--;
                        if (loading_widgets_cnt === 0)
                        {
                            that.renderAggregatePositions();
                        }
                    })
                );
            }
        });

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

        item.find('.input-group-label').click(function(expand_event)
        {
            if (item.hasClass('collapsed'))
            {
                item.removeClass('collapsed');
                var first_input = item.find('input').first();
                first_input.focus();
                $('html, body').animate({scrollTop: first_input.offset().top - 200}, 350, function()
                {
                    first_input.focus();
                });
            }
            else
            {
                item.addClass('collapsed');
            }
        });
    },

    moveItemUp: function(item)
    {
        item.insertBefore(item.prev());
        this.renderAggregatePositions();
        this.fire('aggregate-moved-up', [{
            'field': this.options.fieldname,
            'element': item
        }]);
    },

    moveItemDown: function(item)
    {
        item.insertAfter(item.next());
        this.renderAggregatePositions();
        this.fire('aggregate-moved-down', [{
            'field': this.options.fieldname,
            'element': item
        }]);
    },

    removeItem: function(item)
    {
        item.remove();
        this.renderAggregatePositions();

        this.fire('aggregate-removed', [{
            'field': this.options.fieldname,
            'element': item[0]
        }]);
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
            for (; i < element.widgets.length; i++)
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
