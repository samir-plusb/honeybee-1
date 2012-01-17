/**
 * @class
 * @augments midas.items.edit.Input
 * @description The AssistiveTextInput ...
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.AssistiveTextInput = midas.items.edit.Input.extend(
/** @lends midas.items.edit.AssistiveTextInput.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'AssistiveTextInput',

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLForm input, select...
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);
        this.createContextMenu();
    },

    /**
     * @description Creates a contextmenu that is bound to the input's element.
     */
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

    /**
     * @description Returns an object defining the menu items to use
     * inside the context menu that is created for the input.
     * @return {Object}
     */
    getMenuItems: function()
    {
        return [
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