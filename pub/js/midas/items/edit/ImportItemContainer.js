/**
 * @class
 * @augments midas.items.edit.ImportItemContainer
 * @description The ImportItemContainer wraps the current import item data providing simple api to set and retrieve data.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.ImportItemContainer = midas.core.BaseObject.extend(
/** @lends midas.items.edit.ImportItemContainer.prototype */
{
    log_prefix: 'ImportItemContainer',

    /**
     * Holds a jquery element, that holds our root container.
     * @type jQuery
     */
    element: null,

    /**
     * Holds a jquery element, that holds our import item metadata-panel.
     * @type jQuery
     */
    meta_data_panel: null,

    /**
     * Holds a jquery element, that holds our import item content-panel.
     * @type jQuery
     */
    content_data_panel: null,

    /**
     * Holds a jquery element, that holds our import item text content.
     * @type jQuery
     */
    content_text_input: null,

    init: function(element, options)
    {
        this.parent(options);
        this.element = element;

        this.content_panel = this.element.find(this.options.tabs_container);
        this.content_panel.find('.legend').css('display', 'none');

        this.content_text_input = new midas.items.edit.AssistiveTextInput(
            this.content_panel.find('textarea').first()
        );
        this.content_text_input.on('contextMenuSelect', function(field, item)
        {
            this.fire('contextMenuSelect', [field, item]);
        }.bind(this));

        this.content_panel.tabs();
    }
});

