/**
 * @class
 * @augments midas.items.edit.AssistiveTextInput
 * @description The AssistiveTextInput ...
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.AssistiveTextInput = midas.items.edit.Input.extend(
/** @lends midas.items.edit.AssistiveTextInput.prototype */
{
    log_prefix: '',

    init: function(element, options)
    {
        this.parent(element, options);
        this.createContextMenu();
    },

    createContextMenu: function()
    {
        var items = this.getMenuItems();
        var prepared_items = {};
        var item;

        for (var i = 0; i < items.length; i++)
        {
            item = items[i];
            prepared_items[item.label] = {
                'click': function(item)
                {
                    this.fire('contextMenuSelect', [this, item]);
                }.bind(this, item)
            };

            if (item['class'])
            {
                prepared_items[item.label].klass = item['class'];
            }
        }

        this.element.contextMenu(
            'content-data-menu-'+(this.getName() || 'default'),
            prepared_items,
            { disable_native_context_menu: false, leftClick: false }
        );
    },

    getMenuItems: function()
    {
        return [
            { 'key': 'new_item', 'label': 'neues Item aus Auswahl', 'class': 'menu-item-break' },
            { 'key': 'localize_item', 'label': 'lokalisieren', 'class': 'menu-item-break' },
            { 'key': 'set_title', 'label': 'als Überschrift setzen' },
            { 'key': 'append_title', 'label': 'an Überschrift anhängen' },
            { 'key': 'set_text', 'label': 'als Textkörper setzen' },
            { 'key': 'append_text', 'label': 'an Textkörper anhängen' },
            { 'key': 'set_url', 'label': 'als Url setzen', 'class': 'menu-item-break' },
            { 'key': 'set_startdate', 'label': 'als Startdatum setzen' },
            { 'key': 'set_enddate', 'label': 'als Enddatum setzen', 'class': 'menu-item-break' },
            { 'key': 'remove_hyphens', 'label': 'Bindestriche entfernen' },
            { 'key': 'remove_linefeeds', 'label': 'Umbrüche entfernen' }
        ];
    }
});