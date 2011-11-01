/**
 * @class
 * @augments midas.items.edit.ContentItemsList
 * @description The ContentItemsList ...
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.ContentItemsList = midas.core.BaseObject.extend(
/** @lends midas.items.edit.ContentItemsList.prototype */
{
    log_prefix: 'ContentItemsList',

    element: null,

    content_item_list: null,

    content_items: null,

    init: function(element, options)
    {
        this.parent(options);
        this.element = element;
        this.element.delegate('li', 'click', function(event)
        {
            var item = this.content_items[event.currentTarget._data_idx];
            this.fire('itemClicked', [item]);
        }.bind(this));
        this.content_items = [];

        if (! this.options.items_template)
        {
            this.options.items_template = 'content-item-tpl';
        }

        if ($.isArray(this.options.items))
        {
            for (var i = 0; i < this.options.items.length; i++)
            {
                this.add(this.options.items[i]);
            }
        }
        this.updateGui();
    },

    add: function(item)
    {
        var rendered_item = ich['content-item-tpl'](item);
        this.content_items.push({
            element: rendered_item,
            data: item
        });
        rendered_item[0]._data_idx = this.content_items.length - 1;
        this.element.append(rendered_item);
        this.updateGui();
    },

    remove: function(item_id)
    {

    },

    updateGui: function()
    {
        if (this.options.state_display)
        {
            this.options.state_display.text(this.content_items.length);
        }
    }
});