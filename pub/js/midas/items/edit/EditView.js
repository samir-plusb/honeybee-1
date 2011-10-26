/**
 * @class
 * @augments midas.core.BaseObject
 * @description The EditView module manages all behaviour for the system's Items/EditSuccessView.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditView = midas.core.BaseView.extend(
/** @lends midas.items.edit.EditView.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "EditView",

    /**
     * Holds a jquery element, that represents our import item content-panel.
     * @type jQuery
     */
    content_panel: null,

    /**
     * Represents a set of commands that can be triggered for manipulating content-item state.
     * @type midas.core.CommandTriggerList
     */
    content_item_menu: null,

    /**
     * Represents a set of commands that can be triggered for manipulating import-item state.
     * @type midas.core.CommandTriggerList
     */
    import_item_menu: null,

    /**
     * Holds an SlidePanel instance used to slide our content.
     * @type midas.items.edit.SlidePanel
     */
    slide_panel: null,

    /**
     * Holds a jquery element, that represents our content-item editing form.
     * @type midas.items.edit.EditForm
     */
    editing_form: null,

    /**
     * @description <p>Initializes our gui (behaviours).</p>
     * <p>This method is invoked from midas.core.BaseView, upon init invocation.</p>
     */
    onInitGui: function()
    {
        this.editing_form = new midas.items.edit.EditForm(
            $('.document-editing form', this.layout_root)
        );

        this.slide_panel = new midas.items.edit.SlidePanel(
            $('.slide-panel', this.layout_root)
            .css({ 'position': 'absolute', 'width': '100%' }),
            { range: '20em' }
        );

        // Set our content-items-list container position to be just outside our viewport,
        // so it will slide in to view when our slide_panel's slideIn method is executed.
        var items_container = $('.content-items').first();
        items_container.css('left', -items_container.outerWidth());

        this.initMenus();
        this.initContentTabs();
    },

    /**
     * @description Initializes the jquery-ui tabs for our content_panel.
     */
    initContentTabs: function()
    {
        this.content_panel = $(this.options.tabs_container, this.layout_root);
        $('.legend', this.content_panel[0]).css('display', 'none');
        this.content_panel.tabs();
    },

    /**
     * @description Initializes the content-item and import-item menu.
     */
    initMenus: function()
    {
        this.content_item_menu = new midas.core.CommandTriggerList(
            $('#content-item-menu').first()[0],
            { 'commands': this.getContentItemCommandBindings() }
        );

        this.import_item_menu = new midas.core.CommandTriggerList(
            $('#import-item-menu').first()[0],
            { 'commands': this.getImportItemCommandBindings() }
        );
    },

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating content-item state.
     * @returns {Object}
     */
    getContentItemCommandBindings: function()
    {
        return {
            'store': this.onStoreContentItem.bind(this),
            'delete': this.onDeleteContentItem.bind(this),
            'new': function() { this.logDebug("onImportDeletePrev"); }.bind(this),
            'list': this.slide_panel.toggle.bind(this.slide_panel)
        };
    },

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating import-item state.
     * @returns {Object}
     */
    getImportItemCommandBindings: function()
    {
        return {
            'prev': function() { this.logDebug('onImportItemPrev'); }.bind(this),
            'delete': function() { this.logDebug("onImportDeletePrev"); }.bind(this),
            'mark': function() { this.logDebug('onImportItemPrev'); }.bind(this),
            'next': function() { this.logDebug('onImportItemNext'); }.bind(this)
        };
    },

    attachController: function(controller)
    {
        this.controller = controller;
    },

    onStoreContentItem: function()
    {
        if (! this.controller)
        {
            this.logWarning("No controller attached to view.");
            return;
        }

        this.controller.applyIntent({ 'name': 'storeContentItem', 'src': this });
    },

    onDeleteContentItem: function()
    {
        if (! this.controller)
        {
            this.logWarning("No controller attached to view.");
            return;
        }

        this.controller.applyIntent({ 'name': 'storeDeleteItem', 'src': this });
    }
});