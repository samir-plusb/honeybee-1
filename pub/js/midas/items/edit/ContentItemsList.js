/**
 * @class
 * @augments midas.core.BaseObject
 * @description The ContentItemsList provides rendering of and access to a collection of content items.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.ContentItemsList = midas.core.BaseObject.extend(
/** @lends midas.items.edit.ContentItemsList.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'ContentItemsList',

    /**
     * The html list element that is used to render the content items collection.
     * @type HTMLUlElement
     */
    element: null,

    /**
     * An object holding the content item collection inside an { [id]: [item] } structure,
     * thereby holding an additional key 'length' that holds the total number of content items.
     * @type {Object}
     */
    content_items: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLUlElement that serves as the ui base for our list.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = element;
        this.element.delegate('li', 'click', function(event)
        {
            var item = this.content_items[event.currentTarget.cid];
            this.fire('itemClicked', [item]);
        }.bind(this));
        this.element.delegate('li', 'mouseenter', function(event)
        {
            var item = this.content_items[event.currentTarget.cid];
            this.fire('itemEnter', [item]);
        }.bind(this));
        this.element.delegate('li', 'mouseleave', function(event)
        {
            var item = this.content_items[event.currentTarget.cid];
            this.fire('itemLeave', [item]);
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
        this.updateStatusDisplay();
    },

    /**
     * @description Add a content item to the list.
     * @param {Objet} item The content item to add.
     */
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
            this.updateStatusDisplay();
        }
    },

    /**
     * @description Remove a content item from the list.
     * @param {Number} cid The cid of the content item to remove.
     */
    remove: function(cid)
    {
        if (this.content_items[cid])
        {
            this.content_items.length--;
            this.content_items[cid].element.remove();
            delete this.content_items[cid];
            this.updateStatusDisplay();
        }
    },

    size: function()
    {
        return this.content_items.length;
    },

    /**
     * @description Get a content item by cid.
     * @param {Number} cid The cid of the content item to get.
     */
    getItem: function(cid)
    {
        return this.content_items[cid] || null;
    },

    /**
     * @description Update the status display.
     */
    updateStatusDisplay: function()
    {
        if (this.options.state_display)
        {
            this.options.state_display.text(this.content_items.length);
        }
    }
});