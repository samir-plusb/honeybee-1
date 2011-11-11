/**
 * @class
 * @augments midas.core.BaseObject
 * @description The ImportItemContainer wraps the current import item data and provides some simple beahviour for it,
 * such as creating the tab control and import content context-menu.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.ImportItemContainer = midas.core.BaseObject.extend(
/** @lends midas.items.edit.ImportItemContainer.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
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
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The import item wrapper.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = element;
        this.content_panel = this.element.find(this.options.tabs_container);
        this.content_panel.find('.legend').css('display', 'none');
        this.content_text_input = new midas.items.edit.AssistiveTextInput(
            this.content_panel.find('textarea').first()
        ).on('contextMenuSelect', function(field, item)
        {
            this.fire('contextMenuSelect', [field, item]);
        }.bind(this));
        this.content_panel.tabs();
    }
});

