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
            var item = this.content_items[event.currentTarget.cid];
            this.fire('itemClicked', [item]);
        }.bind(this));
        this.content_items = {
            length: 0
        };

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
        if (! item.cid)
        {
            throw "Can not item that has no valid cid.";
        }

        var rendered_html = ich['content-item-tpl'](item, true);
        var tmp_item = $('<div></div>').html(rendered_html.replace('&gt;', '>').replace('&lt;', '<'));

        var rendered_item = tmp_item.find('li');
        rendered_item[0].cid = item.cid;

        if (this.content_items[item.cid]) // update item
        {
            this.content_items[item.cid].element.replaceWith(rendered_item);
            this.content_items[item.cid] = {
                element: rendered_item,
                data: item
            };
        }
        else // create item
        {
            this.content_items[item.cid] = {
                element: rendered_item,
                data: item
            };
            this.content_items.length++;
            this.element.append(rendered_item);
            this.updateGui();
        }
    },

    remove: function(cid)
    {
        if (this.content_items[cid])
        {
            this.content_items.length--;
            this.content_items[cid].element.remove();
            delete this.content_items[cid];
            this.updateGui();
        }
    },

    getItem: function(cid)
    {
        return this.content_items[cid] || null;
    },

    updateGui: function()
    {
        if (this.options.state_display)
        {
            this.options.state_display.text(this.content_items.length);
        }
    }
});