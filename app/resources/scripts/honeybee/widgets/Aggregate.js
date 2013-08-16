honeybee.widgets.Aggregate = honeybee.widgets.Widget.extend({

    log_prefix: "Aggregate",

    fieldname: null,

    aggregate_list: null,

    templates: null,

    init: function(element, options, ready_callback)
    {
        this.templates = {};
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

    addAggregate: function(doc_type)
    {
        var module_item_markup = this.templates[doc_type];
        var list_item = $('<li class="row-fluid aggregate"></li>');
        list_item.html(module_item_markup);
        this.aggregate_list.append(list_item);

        this.initAggregateListItem(list_item);
        this.renderAggregatePositions();

        var first_input = list_item.find('input').first();
        first_input.focus();
        $('html, body').animate({scrollTop: first_input.offset().top}, 350);

        return list_item;
    },

    initAggregateListItem: function(aggregate_element)
    {
        this.registerAggregateEvents(aggregate_element);
        this.registerDisplayedTextInputs(aggregate_element);
    },

    registerAggregateEvents: function(item)
    {
        var that = this;
        item.find('.actions .aggregate-remove').click(function(remove_event)
        {
            item.remove();
            that.renderAggregatePositions();

            that.fire('aggregate-removed', [{
                'field': that.options.fieldname,
                'element': item[0]
            }]);
        });

        item.find('.actions .aggregate-up').click(function(move_up_event)
        {
            item.insertBefore(item.prev());
            that.renderAggregatePositions();
        });

        item.find('.actions .aggregate-down').click(function(move_down_event)
        {
            item.insertAfter(item.next());
            that.renderAggregatePositions();
        });

        item.find('.actions .aggregate-expand').click(function(expand_event)
        {
            item.removeClass('collapsed');

            var first_input = item.find('input').first();
            first_input.focus();
            $('html, body').animate({scrollTop: first_input.offset().top}, 350);
        });

        item.find('.actions .aggregate-collapse').click(function(collapse_event)
        {
            item.addClass('collapsed');
        });
    },

    renderAggregatePositions: function()
    {
        this.element.find('> .aggregates-list .aggregate').each(function(idx, element)
        {
            $(element).find('.input-group-label .position').text('#' + (idx + 1));
            // @todo include select, radio etc.
            $(element).find('input, textarea').each(function(pos, input)
            {
                $(input).attr(
                    'name',
                    $(input).attr('name').replace(/\[\d+\]/, '[' + idx + ']')
                );
            });
        });
    },

    getTemplate: function()
    {
        return false;
    }
});
